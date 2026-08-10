const form = document.querySelector(".photo-upload-form"),
  isEdit = form?.dataset.edit === "true",
  input = document.querySelector("#files"),
  drop = document.querySelector("#drop"),
  queue = document.querySelector("#queue"),
  fileCount = document.querySelector("#fileCount"),
  publish = document.querySelector("#publish"),
  ready = document.querySelector("#ready"),
  removeBtn = document.querySelector("#remove"),
  allBox = document.querySelector("#all");
let items = [];
const fmt = (n) => (n / 1048576).toFixed(1) + " MB";
function sync() {
  if (!input) return;
  const dt = new DataTransfer();
  items.filter((x) => x.on).forEach((x) => dt.items.add(x.file));
  input.files = dt.files;
}
function render() {
  if (isEdit) {
    if (items.length) {
      queue.innerHTML = "";
      items.forEach((x) =>
        queue.insertAdjacentHTML(
          "beforeend",
          `<article><img src="${x.url}"><span><b>${x.file.name}</b><small>${fmt(x.file.size)} · se agregará al set</small></span><em>NUEVA</em></article>`,
        ),
      );
      fileCount.textContent = items.length + " archivos nuevos";
      ready.textContent = items.length + " fotografías listas para agregar";
    } else {
      fileCount.textContent = "SELECCIÓN MÚLTIPLE";
      ready.textContent = "Puedes agregar varias fotografías al set";
    }
    publish.disabled = false;
    sync();
    return;
  }
  if (!queue) return;
  queue.innerHTML = items.length ? "" : "<p>NO HAY ARCHIVOS EN LA COLA</p>";
  items.forEach((x, i) =>
    queue.insertAdjacentHTML(
      "beforeend",
      `<article><input type="checkbox" data-i="${i}" ${x.on ? "checked" : ""}><img src="${x.url}"><span><b>${x.file.name}</b><small>${fmt(x.file.size)}</small></span><em>LISTA</em></article>`,
    ),
  );
  fileCount.textContent = items.filter((x) => x.on).length + " archivos";
  publish.disabled = !items.some((x) => x.on);
  ready.textContent = items.some((x) => x.on)
    ? items.filter((x) => x.on).length + " fotografías listas"
    : "Lote sin archivos";
  queue.querySelectorAll("input").forEach(
    (c) =>
      (c.onchange = () => {
        items[c.dataset.i].on = c.checked;
        sync();
        render();
      }),
  );
  sync();
}
function add(files) {
  const valid = [...files].filter((f) => f.type.startsWith("image/"));
  valid.forEach((file) => {
    const key = [file.name, file.size, file.lastModified].join(":");
    if (!items.some((x) => x.key === key))
      items.push({ file, key, url: URL.createObjectURL(file), on: true });
  });
  render();
}
input?.addEventListener("change", (e) => {
  if (e.target.files.length) add(e.target.files);
});
["dragover", "dragenter"].forEach((name) =>
  drop?.addEventListener(name, (e) => {
    e.preventDefault();
    drop.classList.add("drag");
  }),
);
drop?.addEventListener("dragleave", () => drop.classList.remove("drag"));
drop?.addEventListener("drop", (e) => {
  e.preventDefault();
  drop.classList.remove("drag");
  add(e.dataTransfer.files);
});
removeBtn?.addEventListener("click", () => {
  if (isEdit) {
    items.forEach((x) => URL.revokeObjectURL(x.url));
    items = [];
    if (input) input.value = "";
    render();
    return;
  }
  const removed = items.filter((x) => x.on),
    remaining = items.filter((x) => !x.on);
  removed.forEach((x) => URL.revokeObjectURL(x.url));
  items = remaining;
  if (!items.length && input) input.value = "";
  if (allBox) allBox.checked = items.length > 0 && items.every((x) => x.on);
  render();
});
allBox?.addEventListener("change", () => {
  items.forEach((x) => (x.on = allBox.checked));
  render();
});
const wmInput = document.querySelector("#wmInput"),
  wmText = document.querySelector("#wmText"),
  wmToggle = document.querySelector("#wmToggle"),
  wmImageInput = document.querySelector("#wmImageInput"),
  wmPreview = document.querySelector("#wmPreview"),
  wmConfig = window.ultraWatermarkConfig || {};
let wmImageUrl = "";

if (wmImageInput && wmPreview) {
  wmImageInput.closest("label")?.insertAdjacentHTML(
    "afterend",
    `<div class="wm-controls" id="wmControls">
      <div class="wm-control-heading">
        <strong>AJUSTE DE MARCA DE AGUA</strong>
        <small>El cambio se aplicará a todas las fotos del set.</small>
      </div>
      <label>
        <span><b>TAMAÑO</b><output id="wmScaleValue">${Number(wmConfig.scale) || 90}%</output></span>
        <input id="wmScale" name="watermark_scale" type="range" min="20" max="100" step="5" value="${Number(wmConfig.scale) || 90}">
        <small>Desde discreta hasta cubrir prácticamente toda la fotografía.</small>
      </label>
      <label>
        <span><b>OPACIDAD</b><output id="wmOpacityValue">${Number(wmConfig.opacity) || 65}%</output></span>
        <input id="wmOpacity" name="watermark_opacity" type="range" min="10" max="100" step="5" value="${Number(wmConfig.opacity) || 65}">
        <small>Reduce o aumenta la intensidad visual del logo.</small>
      </label>
      <p class="wm-control-note">Selecciona el archivo del logo para ver los cambios en tiempo real.</p>
    </div>`,
  );
  wmPreview.insertAdjacentHTML(
    "beforeend",
    '<img class="wm-image-overlay" id="wmImagePreview" alt="Vista previa de la marca de agua" hidden>',
  );
}

const wmControls = document.querySelector("#wmControls"),
  wmScale = document.querySelector("#wmScale"),
  wmOpacity = document.querySelector("#wmOpacity"),
  wmScaleValue = document.querySelector("#wmScaleValue"),
  wmOpacityValue = document.querySelector("#wmOpacityValue"),
  wmImagePreview = document.querySelector("#wmImagePreview");

function watermarkFields() {
  const image = document.querySelector(
    '[name="watermark_type"][value="image"]',
  )?.checked;
  const enabled = wmToggle?.checked ?? true;
  if (wmImageInput?.closest("label"))
    wmImageInput.closest("label").hidden = !image;
  if (wmInput?.closest("label")) wmInput.closest("label").hidden = !!image;
  if (wmControls) wmControls.hidden = !image;
  if (wmText) {
    wmText.textContent = wmInput?.value || "ULTRA MEDIA DIGITAL";
    wmText.hidden = !enabled || !!image;
  }
  if (wmImagePreview) {
    wmImagePreview.hidden = !enabled || !image || !wmImageUrl;
    wmImagePreview.style.width = `${wmScale?.value || 90}%`;
    wmImagePreview.style.height = `${wmScale?.value || 90}%`;
    wmImagePreview.style.opacity = String((Number(wmOpacity?.value) || 65) / 100);
  }
  if (wmScaleValue) wmScaleValue.value = `${wmScale?.value || 90}%`;
  if (wmOpacityValue)
    wmOpacityValue.value = `${wmOpacity?.value || 65}%`;
  wmPreview?.classList.toggle("is-disabled", !enabled);
}

wmInput?.addEventListener("input", watermarkFields);
wmToggle?.addEventListener("change", watermarkFields);
wmScale?.addEventListener("input", watermarkFields);
wmOpacity?.addEventListener("input", watermarkFields);
wmImageInput?.addEventListener("change", (event) => {
  const file = event.target.files[0];
  if (wmImageUrl) URL.revokeObjectURL(wmImageUrl);
  wmImageUrl = file ? URL.createObjectURL(file) : "";
  if (wmImagePreview) wmImagePreview.src = wmImageUrl;
  watermarkFields();
});
document
  .querySelectorAll('[name="watermark_type"]')
  .forEach((r) => r.addEventListener("change", watermarkFields));
watermarkFields();
render();
