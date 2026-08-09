<?php if(!empty($_SESSION['error'])):?>
<div class="demo-note product-alert"><?=htmlspecialchars($_SESSION['error']);unset($_SESSION['error']);?></div>
<?php endif;?>

<nav class="crumb product-crumb" aria-label="Navegación">
 <a href="<?=url()?>">INICIO</a><span>›</span><a href="<?=url('eventos')?>">EVENTOS</a><span>›</span><b><?=htmlspecialchars($photo['set_name']?:$photo['title'])?></b>
</nav>

<section class="product-detail product-detail--without-cover">
 <div class="detail-copy product-summary">
  <div class="product-summary-main">
   <div class="product-heading">
    <span class="eyebrow dark">FOTOGRAFÍA DEPORTIVA OFICIAL</span>
    <h1><?=htmlspecialchars($photo['set_name']?:$photo['title'])?></h1>
    <div class="product-context">
     <span><small>EVENTO</small><b><?=htmlspecialchars($photo['event_name'])?></b></span>
     <?php if($photo['bib_number']!==null&&$photo['bib_number']!==''):?><span><small>COMPETIDOR</small><b>#<?=htmlspecialchars($photo['bib_number'])?></b></span><?php endif;?>
     <span><small>SET</small><b><?=count($related)?> <?=count($related)===1?'FOTO':'FOTOS'?></b></span>
    </div>
   </div>
  </div>

  <div class="product-summary-side">
   <div class="product-benefits" aria-label="Características de la compra">
    <div><i>01</i><span><b>ALTA RESOLUCIÓN</b><small>Original sin marca de agua</small></span></div>
    <div><i>02</i><span><b>PAGO SEGURO</b><small>Procesado mediante Flow</small></span></div>
    <div><i>03</i><span><b>DESCARGA DIGITAL</b><small>Disponible después del pago</small></span></div>
   </div>

   <div class="purchase-guide">
    <div><span>COMPRA A TU MEDIDA</span><h2>ELIGE CÓMO QUIERES TUS FOTOS</h2><p>Selecciona fotografías individuales, activa automáticamente un pack o lleva el set completo.</p></div>
    <a href="#opciones-compra">VER OPCIONES <b aria-hidden="true">↓</b></a>
   </div>
  </div>
 </div>

 <div class="product-purchase" id="opciones-compra">
  <?php require ROOT.'/app/Views/partials/product_selection.php';?>

  <?php if($photo['set_id']&&!empty($photo['set_enabled'])):?>
  <div class="set-box detail-set-box">
   <div class="detail-set-copy">
    <span>MEJOR VALOR · TODO INCLUIDO</span>
    <h2>LLÉVATE EL SET COMPLETO</h2>
    <p><?=count($related)?> fotografías originales en alta resolución, reunidas en un único archivo ZIP.</p>
   </div>
   <div class="set-includes">
    <span><b>✓</b> Todas las fotografías</span>
    <span><b>✓</b> Archivos sin marca de agua</span>
    <span><b>✓</b> Una descarga organizada</span>
   </div>
   <div class="detail-set-action">
    <strong><small>VALOR DEL SET</small><?=money((int)$photo['set_price'])?></strong>
    <form method="post" action="<?=url('carrito/agregar')?>">
     <input type="hidden" name="_token" value="<?=csrf()?>">
     <input type="hidden" name="type" value="set">
     <input type="hidden" name="id" value="<?=$photo['set_id']?>">
     <button>COMPRAR SET COMPLETO <span aria-hidden="true">→</span></button>
    </form>
   </div>
  </div>
  <?php endif;?>
 </div>
</section>
