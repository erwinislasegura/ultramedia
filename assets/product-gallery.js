(()=>{
 document.querySelectorAll('[data-product-slider]').forEach((slider,index)=>{const images=[...slider.querySelectorAll('img')];if(images.length<2)return;let current=0;setInterval(()=>{images[current].classList.remove('active');current=(current+1)%images.length;images[current].classList.add('active')},2800+(index%3)*350)});
 const main=document.getElementById('detailMainImage');
 document.querySelectorAll('.detail-thumb').forEach(button=>button.addEventListener('click',()=>{document.querySelectorAll('.detail-thumb').forEach(x=>x.classList.remove('active'));button.classList.add('active');if(main)main.src=button.dataset.image}));
 const checks=[...document.querySelectorAll('.photo-selector input[type="checkbox"]')],count=document.getElementById('selectedCount'),total=document.getElementById('selectedTotal'),submit=document.getElementById('addSelected');
 const money=value=>'$'+new Intl.NumberFormat('es-CL').format(value);
 function updateIndividual(){const selected=checks.filter(x=>x.checked);if(count)count.textContent=selected.length;if(total)total.textContent=money(selected.reduce((sum,x)=>sum+Number(x.dataset.price||0),0));if(submit)submit.disabled=selected.length===0}
 checks.forEach(check=>check.addEventListener('change',updateIndividual));updateIndividual();
 document.querySelectorAll('[data-pack-limit]').forEach(form=>{
  const limit=Number(form.dataset.packLimit),options=[...form.querySelectorAll('.pack-photo-selector input')],counter=form.querySelector('.pack-selected-count'),bar=form.querySelector('.pack-progress b'),button=form.querySelector('button[type="submit"]');
  function updatePack(){const selected=options.filter(x=>x.checked),complete=selected.length===limit;counter.textContent=selected.length;bar.style.width=Math.min(100,selected.length/limit*100)+'%';options.forEach(x=>x.disabled=!x.checked&&selected.length>=limit);button.disabled=!complete;button.textContent=complete?'AGREGAR PACK AL CARRITO →':`SELECCIONA ${limit-selected.length} FOTO${limit-selected.length===1?'':'S'} MÁS`;}
  options.forEach(option=>option.addEventListener('change',updatePack));updatePack();
 });
})();
