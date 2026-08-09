<?php
namespace App\Models;

use App\Core\Database;
use PDO;
use Throwable;

final class Event
{
    private static ?bool $moduleReady = null;

    public static function ensureModule(): bool
    {
        if (self::$moduleReady !== null) {
            return self::$moduleReady;
        }

        $db = Database::db();
        try {
            $hadRelations = (bool)$db->query("SHOW TABLES LIKE 'event_sets'")->fetchColumn();
            $db->exec("CREATE TABLE IF NOT EXISTS event_catalog(
                event_id INT UNSIGNED PRIMARY KEY,
                cover_path VARCHAR(500) NULL,
                description VARCHAR(700) NULL,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                CONSTRAINT fk_event_catalog_event FOREIGN KEY(event_id) REFERENCES events(id) ON DELETE CASCADE
            ) ENGINE=InnoDB");
            $db->exec("CREATE TABLE IF NOT EXISTS event_sets(
                event_id INT UNSIGNED NOT NULL,
                set_id BIGINT UNSIGNED NOT NULL,
                position SMALLINT UNSIGNED NOT NULL DEFAULT 0,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY(event_id,set_id),
                UNIQUE KEY uq_event_set(set_id),
                INDEX idx_event_sets_position(event_id,position),
                CONSTRAINT fk_event_sets_event FOREIGN KEY(event_id) REFERENCES events(id) ON DELETE CASCADE,
                CONSTRAINT fk_event_sets_set FOREIGN KEY(set_id) REFERENCES photo_sets(id) ON DELETE CASCADE
            ) ENGINE=InnoDB");
            if (!$hadRelations) {
                $db->exec("INSERT IGNORE INTO event_sets(event_id,set_id,position)
                    SELECT ps.event_id,ps.id,ps.id FROM photo_sets ps");
            }
            return self::$moduleReady = true;
        } catch (Throwable $e) {
            error_log('Event module schema: '.$e->getMessage());
            return self::$moduleReady = false;
        }
    }

    public static function adminEvents(): array
    {
        $db = Database::db();
        $ready = self::ensureModule();
        $catalog = $ready ? 'LEFT JOIN event_catalog ec ON ec.event_id=e.id' : '';
        $catalogFields = $ready ? ',ec.cover_path,ec.description' : ",NULL cover_path,NULL description";
        $events = $db->query("SELECT e.*$catalogFields FROM events e $catalog ORDER BY e.event_date DESC,e.id DESC")->fetchAll();

        foreach ($events as &$event) {
            $sets = self::setsForEvent((int)$event['id'], false);
            $event['sets_count'] = count($sets);
            $event['photos_count'] = array_sum(array_map(static fn(array $set): int => (int)$set['photos_count'], $sets));
            $event['cover_id'] = $sets[0]['cover_id'] ?? null;
            $event['preview_path'] = $sets[0]['preview_path'] ?? null;
        }
        unset($event);
        return $events;
    }

    public static function findAdmin(int $id): ?array
    {
        if ($id < 1) {
            return null;
        }
        $db = Database::db();
        $ready = self::ensureModule();
        $sql = $ready
            ? 'SELECT e.*,ec.cover_path,ec.description FROM events e LEFT JOIN event_catalog ec ON ec.event_id=e.id WHERE e.id=?'
            : 'SELECT e.*,NULL cover_path,NULL description FROM events e WHERE e.id=?';
        $statement = $db->prepare($sql);
        $statement->execute([$id]);
        return $statement->fetch() ?: null;
    }

    public static function allSets(): array
    {
        $db = Database::db();
        $cover = self::coverExpression($db);
        return $db->query("SELECT ps.*,e.name source_event_name,COUNT(p.id) photos_count,$cover cover_id,MIN(p.preview_path) preview_path
            FROM photo_sets ps
            JOIN events e ON e.id=ps.event_id
            LEFT JOIN photos p ON p.set_id=ps.id
            GROUP BY ps.id
            ORDER BY ps.status='active' DESC,ps.id DESC")->fetchAll();
    }

    public static function selectedSetIds(int $eventId): array
    {
        $db = Database::db();
        if (self::ensureModule()) {
            $catalog = $db->prepare('SELECT event_id FROM event_catalog WHERE event_id=?');
            $catalog->execute([$eventId]);
            if ($catalog->fetchColumn()) {
                $statement = $db->prepare('SELECT set_id FROM event_sets WHERE event_id=? ORDER BY position,set_id');
                $statement->execute([$eventId]);
                return array_map('intval', $statement->fetchAll(PDO::FETCH_COLUMN));
            }
        }
        $statement = $db->prepare('SELECT id FROM photo_sets WHERE event_id=? ORDER BY id');
        $statement->execute([$eventId]);
        return array_map('intval', $statement->fetchAll(PDO::FETCH_COLUMN));
    }

    public static function saveComposition(int $eventId, string $description, ?string $coverPath, array $setIds): void
    {
        if (!self::ensureModule()) {
            throw new \RuntimeException('Debes ejecutar database/update_events_module.sql antes de guardar el evento.');
        }

        $db = Database::db();
        $setIds = array_values(array_unique(array_filter(array_map('intval', $setIds), static fn(int $id): bool => $id > 0)));
        if ($setIds) {
            $marks = implode(',', array_fill(0, count($setIds), '?'));
            $valid = $db->prepare("SELECT id FROM photo_sets WHERE id IN ($marks)");
            $valid->execute($setIds);
            $validIds = array_map('intval', $valid->fetchAll(PDO::FETCH_COLUMN));
            $setIds = array_values(array_filter($setIds, static fn(int $id): bool => in_array($id, $validIds, true)));
        }

        $db->prepare("INSERT INTO event_catalog(event_id,cover_path,description) VALUES(?,?,?)
            ON DUPLICATE KEY UPDATE cover_path=VALUES(cover_path),description=VALUES(description)")
            ->execute([$eventId, $coverPath ?: null, $description ?: null]);

        $db->prepare('DELETE FROM event_sets WHERE event_id=?')->execute([$eventId]);
        if (!$setIds) {
            return;
        }

        $marks = implode(',', array_fill(0, count($setIds), '?'));
        $db->prepare("DELETE FROM event_sets WHERE set_id IN ($marks)")->execute($setIds);
        $insert = $db->prepare('INSERT INTO event_sets(event_id,set_id,position) VALUES(?,?,?)');
        foreach ($setIds as $position => $setId) {
            $insert->execute([$eventId, $setId, $position + 1]);
        }

        $args = [$eventId, ...$setIds];
        $db->prepare("UPDATE photo_sets SET event_id=? WHERE id IN ($marks)")->execute($args);
        $db->prepare("UPDATE photos SET event_id=? WHERE set_id IN ($marks)")->execute($args);
    }

    public static function publishedEvents(?int $limit = null): array
    {
        $db = Database::db();
        $ready = self::ensureModule();
        $sql = $ready
            ? "SELECT e.*,ec.cover_path,ec.description FROM events e LEFT JOIN event_catalog ec ON ec.event_id=e.id WHERE e.status='published' ORDER BY e.event_date DESC,e.id DESC"
            : "SELECT e.*,NULL cover_path,NULL description FROM events e WHERE e.status='published' ORDER BY e.event_date DESC,e.id DESC";
        $events = $db->query($sql)->fetchAll();
        $visible = [];
        foreach ($events as $event) {
            $sets = self::setsForEvent((int)$event['id'], true);
            if (!$sets) {
                continue;
            }
            $event['sets_count'] = count($sets);
            $event['photos_count'] = array_sum(array_map(static fn(array $set): int => (int)$set['photos_count'], $sets));
            $event['cover_id'] = $sets[0]['cover_id'] ?? null;
            $event['preview_path'] = $sets[0]['preview_path'] ?? null;
            $visible[] = $event;
            if ($limit !== null && count($visible) >= $limit) {
                break;
            }
        }
        return $visible;
    }

    public static function findPublic(string|int $identifier): ?array
    {
        $db = Database::db();
        $ready = self::ensureModule();
        $field = is_numeric($identifier) ? 'e.id' : 'e.slug';
        $sql = $ready
            ? "SELECT e.*,ec.cover_path,ec.description FROM events e LEFT JOIN event_catalog ec ON ec.event_id=e.id WHERE $field=? AND e.status='published'"
            : "SELECT e.*,NULL cover_path,NULL description FROM events e WHERE $field=? AND e.status='published'";
        $statement = $db->prepare($sql);
        $statement->execute([$identifier]);
        return $statement->fetch() ?: null;
    }

    public static function publicSets(int $eventId): array
    {
        return self::setsForEvent($eventId, true);
    }

    public static function previewsForSets(array $sets, int $limit = 4): array
    {
        $ids = array_values(array_filter(array_map(static fn(array $set): int => (int)$set['id'], $sets)));
        if (!$ids) {
            return [];
        }
        $db = Database::db();
        $marks = implode(',', array_fill(0, count($ids), '?'));
        $order = 'p.set_id,p.id';
        try {
            $db->query('SELECT cover_photo_id FROM photo_sets LIMIT 0');
            $order = 'p.set_id,(p.id=ps.cover_photo_id) DESC,p.id';
        } catch (Throwable $e) {
        }
        $statement = $db->prepare("SELECT p.id,p.set_id,p.preview_path,p.title FROM photos p JOIN photo_sets ps ON ps.id=p.set_id WHERE p.set_id IN ($marks) ORDER BY $order");
        $statement->execute($ids);
        $previews = [];
        foreach ($statement->fetchAll() as $row) {
            if (count($previews[$row['set_id']] ?? []) < $limit) {
                $previews[$row['set_id']][] = $row;
            }
        }
        return $previews;
    }

    private static function setsForEvent(int $eventId, bool $onlyPublished): array
    {
        $db = Database::db();
        $ready = self::ensureModule();
        $configured = false;
        if ($ready) {
            $catalog = $db->prepare('SELECT event_id FROM event_catalog WHERE event_id=?');
            $catalog->execute([$eventId]);
            $configured = (bool)$catalog->fetchColumn();
        }

        $cover = self::coverExpression($db);
        $status = $onlyPublished ? " AND ps.status='active'" : '';
        if ($ready && $configured) {
            $statement = $db->prepare("SELECT ps.*,source.name source_event_name,COUNT(p.id) photos_count,$cover cover_id,MIN(p.preview_path) preview_path,MIN(p.price) individual_price
                FROM event_sets es
                JOIN photo_sets ps ON ps.id=es.set_id
                JOIN events source ON source.id=ps.event_id
                LEFT JOIN photos p ON p.set_id=ps.id
                WHERE es.event_id=?$status
                GROUP BY ps.id,es.position
                HAVING COUNT(p.id)>0
                ORDER BY es.position,ps.id");
        } else {
            $statement = $db->prepare("SELECT ps.*,source.name source_event_name,COUNT(p.id) photos_count,$cover cover_id,MIN(p.preview_path) preview_path,MIN(p.price) individual_price
                FROM photo_sets ps
                JOIN events source ON source.id=ps.event_id
                LEFT JOIN photos p ON p.set_id=ps.id
                WHERE ps.event_id=?$status
                GROUP BY ps.id
                HAVING COUNT(p.id)>0
                ORDER BY ps.id DESC");
        }
        $statement->execute([$eventId]);
        return $statement->fetchAll();
    }

    private static function coverExpression(PDO $db): string
    {
        try {
            $db->query('SELECT cover_photo_id FROM photo_sets LIMIT 0');
            return 'COALESCE(MAX(CASE WHEN p.id=ps.cover_photo_id THEN p.id END),MIN(p.id))';
        } catch (Throwable $e) {
            return 'MIN(p.id)';
        }
    }
}
