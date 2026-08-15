<div class="content users-content">
    <section class="title">
        <div><span class="eyebrow">CONFIGURACIÓN DE ACCESO</span><h1>USUARIOS Y <em>ROLES.</em></h1><p>Administra el equipo y define los permisos del panel.</p></div>
        <?php if(admin_can('users.manage')): ?><button type="button" class="admin-primary" data-open="userModal" data-create-user>+ NUEVO USUARIO</button><?php endif; ?>
    </section>
    <?php if(!empty($_SESSION['success'])): ?><div class="flash success"><?= htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?></div><?php endif; ?>
    <?php if(!empty($_SESSION['error'])): ?><div class="flash error"><?= htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></div><?php endif; ?>

    <section class="user-kpis">
        <article><span>USUARIOS TOTALES</span><strong><?= count($users) ?></strong><small>cuentas registradas</small></article>
        <article><span>USUARIOS ACTIVOS</span><strong><?= count(array_filter($users,fn($u)=>$u['status']==='active')) ?></strong><small>con acceso habilitado</small></article>
        <article><span>ROLES CREADOS</span><strong><?= count($roles) ?></strong><small>perfiles de permisos</small></article>
    </section>

    <?php if(admin_can('users.manage')): ?><section class="panel users-panel">
        <div class="panel-head"><div><span class="eyebrow">EQUIPO</span><h2>CUENTAS DE ACCESO</h2></div><button type="button" data-open="userModal" data-create-user>AGREGAR USUARIO</button></div>
        <div class="users-table">
            <div class="user-row user-head"><span>USUARIO</span><span>ROL</span><span>ESTADO</span><span>ÚLTIMO ACCESO</span><span>ACCIONES</span></div>
            <?php foreach($users as $u): ?>
                <div class="user-row">
                    <span class="user-identity"><b><?= strtoupper(substr($u['name'],0,1)) ?></b><i><strong><?= htmlspecialchars($u['name']) ?></strong><small><?= htmlspecialchars($u['email']) ?></small></i></span>
                    <span><em><?= htmlspecialchars($u['role_name']) ?></em></span>
                    <span><i class="user-status <?= $u['status'] ?>"><?= $u['status']==='active'?'ACTIVO':'INACTIVO' ?></i></span>
                    <span><?= htmlspecialchars($u['last_login_at']?date('d/m/Y H:i',strtotime($u['last_login_at'])):'Sin ingreso') ?></span>
                    <span class="user-actions">
                        <button type="button" class="edit-user" data-user='<?= htmlspecialchars(json_encode($u),ENT_QUOTES,'UTF-8') ?>'>EDITAR</button>
                        <?php if($u['id']>1): ?><form method="post" action="<?= url('admin/usuarios/eliminar') ?>" onsubmit="return confirm('¿Eliminar este usuario?')"><input type="hidden" name="_token" value="<?= csrf() ?>"><input type="hidden" name="id" value="<?= $u['id'] ?>"><button>ELIMINAR</button></form><?php endif; ?>
                    </span>
                </div>
            <?php endforeach; ?>
        </div>
    </section><?php endif; ?>

    <?php if(admin_can('roles.manage')): ?><section class="panel roles-panel">
        <div class="panel-head"><div><span class="eyebrow">PERMISOS</span><h2>ROLES DEL SISTEMA</h2></div><button type="button" data-open="roleModal" data-create-role>+ NUEVO ROL</button></div>
        <div class="roles-list">
            <?php foreach($roles as $r):
                $rolePermissions=json_decode((string)$r['permissions'],true);
                $rolePermissions=is_array($rolePermissions)?array_values($rolePermissions):[];
                if(($r['slug']??'')==='administrador')$rolePermissions=array_keys($permissions);
                $roleData=['id'=>(int)$r['id'],'name'=>$r['name'],'slug'=>$r['slug'],'permissions'=>$rolePermissions,'is_system'=>(bool)$r['is_system']];
            ?>
                <article class="role-card">
                    <i>◎</i>
                    <div class="role-card-main">
                        <div class="role-card-title"><h3><?= htmlspecialchars($r['name']) ?></h3><span><?= $r['is_system']?'SISTEMA':'PERSONALIZADO' ?></span></div>
                        <p><?= (int)$r['users_count'] ?> usuario(s) asignado(s)</p>
                        <div class="role-permissions">
                            <?php if(!$rolePermissions): ?><small>SIN PERMISOS ASIGNADOS</small><?php endif; ?>
                            <?php foreach($rolePermissions as $permission): ?><small><?= htmlspecialchars($permissions[$permission]??$permission) ?></small><?php endforeach; ?>
                        </div>
                    </div>
                    <div class="role-card-actions">
                        <button type="button" class="edit-role" data-role='<?= htmlspecialchars(json_encode($roleData),ENT_QUOTES,'UTF-8') ?>'>EDITAR ROL</button>
                        <?php if(($r['slug']??'')!=='administrador'): ?>
                            <form method="post" action="<?= url('admin/roles/eliminar') ?>" onsubmit="return confirm('¿Eliminar definitivamente este rol?')">
                                <input type="hidden" name="_token" value="<?= csrf() ?>"><input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
                                <button class="delete-role" <?= (int)$r['users_count']>0?'disabled title="Reasigna primero los usuarios de este rol"':'' ?>>ELIMINAR</button>
                            </form>
                        <?php endif; ?>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    </section><?php endif; ?>
</div>

<?php if(admin_can('users.manage')): ?><div class="admin-modal" id="userModal"><div>
    <button class="modal-close" type="button">×</button><span class="eyebrow">CUENTA</span><h2 id="userModalTitle">NUEVO USUARIO</h2>
    <form method="post" action="<?= url('admin/usuarios/guardar') ?>">
        <input type="hidden" name="_token" value="<?= csrf() ?>"><input type="hidden" name="id" id="userId">
        <label>NOMBRE<input name="name" id="userName" required></label><label>CORREO<input name="email" id="userEmail" type="email" required></label>
        <section><label>ROL<select name="role_id" id="userRole" required><?php foreach($roles as $r): ?><option value="<?= $r['id'] ?>"><?= htmlspecialchars($r['name']) ?></option><?php endforeach; ?></select></label><label>ESTADO<select name="status" id="userStatus"><option value="active">Activo</option><option value="inactive">Inactivo</option></select></label></section>
        <label>CONTRASEÑA <small>Vacía al editar para conservarla</small><input id="userPassword" name="password" type="password" minlength="8"></label><button class="admin-primary">GUARDAR USUARIO →</button>
    </form>
</div></div><?php endif; ?>

<?php if(admin_can('roles.manage')): ?><div class="admin-modal" id="roleModal"><div>
    <button class="modal-close" type="button">×</button><span class="eyebrow">PERMISOS</span><h2 id="roleModalTitle">NUEVO ROL</h2>
    <form method="post" action="<?= url('admin/roles/guardar') ?>">
        <input type="hidden" name="_token" value="<?= csrf() ?>"><input type="hidden" name="id" id="roleId">
        <label>NOMBRE DEL ROL<input name="name" id="roleName" maxlength="80" required></label><p class="system-role-note" id="systemRoleNote" hidden>El rol Administrador conserva acceso total para evitar que el panel quede bloqueado.</p>
        <div class="permission-list"><?php foreach($permissions as $key=>$label): ?><label><input class="role-permission" type="checkbox" name="permissions[]" value="<?= $key ?>"><span><b><?= htmlspecialchars($label) ?></b><small><?= htmlspecialchars($key) ?></small></span></label><?php endforeach; ?></div>
        <button class="admin-primary">GUARDAR ROL →</button>
    </form>
</div></div><?php endif; ?>
<script src="<?= url('assets/admin-users.js') ?>"></script>
