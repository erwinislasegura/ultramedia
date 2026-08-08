<?php if(!empty($cta)):
$ctaImage=trim((string)($cta['image_url']??''));
if($ctaImage==='')$ctaImage='https://images.unsplash.com/photo-1552674605-db6ffd4facb5?auto=format&fit=crop&w=1800&q=88';
?>
<section class="event-cta-wrap" aria-label="<?=htmlspecialchars($cta['title'])?>">
 <div class="event-cta" style="background-image:linear-gradient(90deg,rgba(5,9,10,.97) 0%,rgba(5,9,10,.88) 43%,rgba(5,9,10,.22) 78%),url('<?=htmlspecialchars($ctaImage)?>')">
  <div class="cta-copy">
   <span><?=htmlspecialchars($cta['eyebrow'])?></span>
   <h2><?=htmlspecialchars($cta['title'])?></h2>
   <p><?=htmlspecialchars($cta['description'])?></p>
   <a href="<?=htmlspecialchars($cta['button_url'])?>"><?=htmlspecialchars($cta['button_text'])?><b>→</b></a>
  </div>
  <aside>
   <small>COLECCIÓN DESTACADA</small>
   <strong>VIVE EL<br>MOMENTO.</strong>
   <i>Fotografías oficiales en alta calidad</i>
  </aside>
 </div>
</section>
<?php endif;?>
