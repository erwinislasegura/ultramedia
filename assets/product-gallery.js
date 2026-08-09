(()=>{
 document.querySelectorAll('[data-product-slider]').forEach((slider,index)=>{
  const images=[...slider.querySelectorAll('img')];
  if(images.length<2)return;
  let current=0;
  setInterval(()=>{
   images[current].classList.remove('active');
   current=(current+1)%images.length;
   images[current].classList.add('active');
  },2800+(index%3)*350);
 });

 const main=document.getElementById('detailMainImage');
 const mainTitle=document.getElementById('detailMainTitle');
 const mainPosition=document.getElementById('detailMainPosition');
 const previewChoices=[...document.querySelectorAll('[data-preview-image]')];

 function showPreview(choice){
  if(!main||!choice)return;
  previewChoices.forEach(item=>item.classList.toggle('is-viewing',item===choice));
  main.src=choice.dataset.previewImage;
  main.alt=choice.dataset.previewTitle||'Vista previa de fotografía';
  main.classList.remove('preview-swap');
  void main.offsetWidth;
  main.classList.add('preview-swap');
  if(mainTitle)mainTitle.textContent=choice.dataset.previewTitle||'';
  if(mainPosition)mainPosition.textContent=`FOTO ${choice.dataset.photoIndex||1} DE ${previewChoices.length}`;
 }

 previewChoices.forEach(choice=>{
  choice.addEventListener('mouseenter',()=>showPreview(choice));
  choice.addEventListener('focusin',()=>showPreview(choice));
  choice.addEventListener('click',()=>showPreview(choice));
  choice.addEventListener('keydown',event=>{
   if(event.key==='Enter'||event.key===' '){
    event.preventDefault();
    const check=choice.querySelector('input[type="checkbox"]');
    if(check){check.checked=!check.checked;check.dispatchEvent(new Event('change',{bubbles:true}));}
    showPreview(choice);
   }
  });
 });

 const initialChoice=previewChoices.find(choice=>choice.classList.contains('is-viewing'))||previewChoices[0];
 if(initialChoice)showPreview(initialChoice);

 const money=value=>'$'+new Intl.NumberFormat('es-CL').format(value);
 document.querySelectorAll('.smart-photo-selection').forEach(form=>{
  const checks=[...form.querySelectorAll('.smart-selector input[type="checkbox"]')];
  const packs=[...form.querySelectorAll('[data-pack-quantity]')].map(node=>({node,quantity:Number(node.dataset.packQuantity),price:Number(node.dataset.packPrice)}));
  const individual=form.dataset.individualEnabled==='1';
  const count=form.querySelector('.smart-count');
  const total=form.querySelector('.smart-total');
  const mode=form.querySelector('.smart-mode');
  const button=form.querySelector('button[type="submit"]');

  function update(){
   const selected=checks.filter(check=>check.checked);
   const pack=packs.find(item=>item.quantity===selected.length);
   count.textContent=selected.length;
   packs.forEach(item=>item.node.classList.toggle('active',item===pack));
   if(pack){
    total.textContent=money(pack.price);
    mode.textContent=`PACK DE ${pack.quantity} ACTIVADO`;
    mode.className='smart-mode pack-active';
    button.disabled=false;
    button.textContent=`AGREGAR PACK DE ${pack.quantity} FOTOS →`;
   }else if(individual&&selected.length){
    total.textContent=money(selected.reduce((sum,check)=>sum+Number(check.dataset.price||0),0));
    mode.textContent='VALOR INDIVIDUAL';
    mode.className='smart-mode';
    button.disabled=false;
    button.textContent='AGREGAR SELECCIÓN AL CARRITO →';
   }else{
    total.textContent='$0';
    mode.textContent=selected.length?'CANTIDAD SIN PACK':'ELIGE TUS FOTOS';
    mode.className='smart-mode';
    button.disabled=true;
    button.textContent='SELECCIONA UNA CANTIDAD DE PACK';
   }
  }

  checks.forEach(check=>check.addEventListener('change',()=>{
   showPreview(check.closest('[data-preview-image]'));
   update();
  }));
  update();
 });
})();
