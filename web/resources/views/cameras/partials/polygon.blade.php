<div class="coordinates !hidden">
    <span class="cc-title">Coordinates</span>
    <span>
        <span class="cc-coord">x: <span class="cc-value" id="x">---</span> | </span>
        <span class="cc-coord">y: <span class="cc-value" id="y">---</span></span>
    </span>
</div>
<div class="image-container mb-4 mt-2">
    <canvas id="canvas" class="m-auto"></canvas>
</div>
<div class="grid grid-cols-6 gap-3 border rounded-md">
    <div class="mode-button mode-toggle flex flex-col items-center justify-center p-2 rounded-md transition-all duration-200 ease-in-out cursor-pointer bg-gray-100 hover:bg-gray-200 active:bg-gray-300 text-gray-700 hover:text-gray-900"
        id="mode-polygon">
        <i class="fa-solid fa-draw-polygon text-xl mb-1"></i>
        <span class="flex flex-col items-center text-nd">
            <span>Polygon Mode</span>
            <span class="text-gray-700 hover:text-gray-900 text-[14px] mt-1">(P)</span>
        </span>
    </div>
    <div class="mode-button mode-toggle flex flex-col items-center justify-center p-2 rounded-md transition-all duration-200 ease-in-out cursor-pointer bg-gray-100 hover:bg-gray-200 active:bg-gray-300 text-gray-700 hover:text-gray-900"
        id="mode-line">
        <i class="fa-solid fa-lines-leaning text-xl mb-1"></i>
        <span class="flex flex-col items-center text-md">
            <span>Line Mode</span>
            <span class="text-gray-700 hover:text-gray-900 text-[14px] mt-1">(L)</span>
        </span>
    </div>
    <div class="mode-button mode-toggle flex flex-col items-center justify-center p-2 rounded-md transition-all duration-200 ease-in-out cursor-pointer bg-gray-100 hover:bg-gray-200 active:bg-gray-300 text-gray-700 hover:text-gray-900"
        id="mode-edit">
        <i class="fa-solid fa-up-down-left-right text-xl mb-1"></i>
        <span class="flex flex-col items-center text-md">
            <span>Edit Mode</span>
            <span class="text-gray-700 hover:text-gray-900 text-[14px] mt-1">(E)</span>
        </span>
    </div>
    <div class="mode-button flex flex-col items-center justify-center p-2 rounded-md transition-all duration-200 ease-in-out cursor-pointer bg-gray-100 hover:bg-gray-200 active:bg-gray-300 text-gray-700 hover:text-gray-900"
        id="undo">
        <i class="fa-solid fa-rotate-left text-xl mb-1"></i>
        <span class="flex flex-col items-center text-md">
            <span>Undo</span>
            <span class="text-gray-700 hover:text-gray-900 text-[14px] mt-1">(Ctrl/⌘-Z)</span>
        </span>
    </div>
    <div class="mode-button flex flex-col items-center justify-center p-2 rounded-md transition-all duration-200 ease-in-out cursor-pointer bg-gray-100 hover:bg-gray-200 active:bg-gray-300 text-gray-700 hover:text-gray-900"
        id="discard-current">
        <i class="fa-solid fa-xmark text-xl mb-1"></i>
        <span class="flex flex-col items-center text-md">
            <span>Discard Current</span>
            <span class="text-gray-700 hover:text-gray-900 text-[14px] mt-1">(Esc)</span>
        </span>
    </div>
    <div class="mode-button flex flex-col items-center justify-center p-2 rounded-md transition-all duration-200 ease-in-out cursor-pointer bg-gray-100 hover:bg-gray-200 active:bg-gray-300 text-gray-700 hover:text-gray-900"
        id="clear">
        <i class="fa-solid fa-trash text-xl mb-1"></i>
        <span class="flex flex-col items-center text-md">
            <span>Clear Polygons</span>
            <span class="text-gray-700 hover:text-gray-900 text-[14px] mt-1">(Ctrl/⌘-E)</span>
        </span>
    </div>
</div>
<script>
    document.querySelectorAll('.mode-toggle').forEach(button => {
        button.addEventListener('click', function() {
            document.querySelectorAll('.mode-toggle').forEach(btn => {
                btn.classList.remove('bg-gray-400', 'text-black');
                btn.classList.add('bg-gray-100', 'text-gray-700');
            });
            this.classList.remove('bg-gray-100', 'text-gray-700');
            this.classList.add('bg-gray-400', 'text-black');
        });
    });

    document.querySelectorAll('.mode-button:not(.mode-toggle)').forEach(button => {
        button.addEventListener('click', function() {
            this.classList.add('bg-gray-400', 'text-black');
            setTimeout(() => {
                this.classList.remove('bg-gray-400', 'text-black');
                this.classList.add('bg-gray-100', 'text-gray-700');
            }, 50);
        });
    });

    document.addEventListener('DOMContentLoaded', function() {
        const polygonButton = document.getElementById('mode-polygon');
        if (polygonButton) {
            polygonButton.click();
        }
    });
</script>
