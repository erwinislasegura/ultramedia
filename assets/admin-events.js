(()=>{
 const input=document.getElementById('eventCoverInput');
 const preview=document.getElementById('eventCoverPreview');
 if(input&&preview)input.addEventListener('change',()=>{
  const file=input.files&&input.files[0];
  if(!file)return;
  preview.style.backgroundImage=`url('${URL.createObjectURL(file)}')`;
  preview.classList.add('has-image');
 });

 const options=document.getElementById('eventSetOptions');
 const search=document.getElementById('eventSetSearch');
 const count=document.getElementById('eventSelectedCount');
 if(!options)return;
 const labels=[...options.querySelectorAll('label[data-set-search]')];
 const checks=[...options.querySelectorAll('input[type="checkbox"]')];
 const updateCount=()=>{if(count)count.textContent=checks.filter(check=>check.checked).length;};
 checks.forEach(check=>check.addEventListener('change',updateCount));
 if(search)search.addEventListener('input',()=>{
  const query=search.value.trim().toLocaleLowerCase('es');
  labels.forEach(label=>label.hidden=query!==''&&!label.dataset.setSearch.includes(query));
 });
 updateCount();
})();
