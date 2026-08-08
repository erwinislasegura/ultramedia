const form=document.getElementById('heroForm');
const preview=document.getElementById('heroPreview');
const opacityValue=document.getElementById('opacityValue');
function refreshHeroPreview(){
 if(!form||!preview)return;
 const data=new FormData(form);
 preview.querySelectorAll('[data-preview]').forEach(node=>node.textContent=data.get(node.dataset.preview)||'');
 const opacity=Math.max(20,Math.min(95,Number(data.get('overlay_opacity'))||75));
 let image=String(data.get('background_url')||'');
 if(image&&!/^https?:\/\//i.test(image))image=`${preview.dataset.baseUrl}${image.replace(/^\//,'')}`;
 image=image.replace(/["'()]/g,encodeURIComponent);
 preview.style.backgroundImage=`linear-gradient(90deg,rgba(8,10,11,${Math.min(.98,opacity/100+.18)}),rgba(8,10,11,${opacity/100}) 55%,rgba(8,10,11,${Math.max(.08,opacity/100-.55)})),url("${image}")`;
 preview.style.backgroundPosition=data.get('background_position')||'center center';
 if(opacityValue)opacityValue.textContent=`${opacity}%`;
}
form?.addEventListener('input',refreshHeroPreview);
form?.addEventListener('change',refreshHeroPreview);
form?.elements.background_image?.addEventListener('change',event=>{
 const file=event.target.files?.[0];
 if(!file)return refreshHeroPreview();
 const reader=new FileReader();
 reader.onload=()=>{const opacity=Math.max(20,Math.min(95,Number(form.elements.overlay_opacity.value)||75))/100;preview.style.backgroundImage=`linear-gradient(90deg,rgba(8,10,11,${Math.min(.98,opacity+.18)}),rgba(8,10,11,${opacity}) 55%,rgba(8,10,11,${Math.max(.08,opacity-.55)})),url("${reader.result}")`;};
 reader.readAsDataURL(file);
});
refreshHeroPreview();
