<script>
	import { SERVER_URL } from "../config";
	import pdfjs from "pdfjs-dist";
	import pdfjsWorker from "pdfjs-dist/build/pdf.worker.entry";

	let canvas;

	pdfjs.GlobalWorkerOptions.workerSrc = pdfjsWorker;

	pdfjs.getDocument("../../server/regulamin.pdf").promise.then(function (pdf) {
		pdf.getPage(1).then((page) => {
			let scale = 1.5;
			let viewport = page.getViewport({ scale: scale });
			let context = canvas.getContext("2d");

			canvas.height = viewport.height;
			canvas.width = viewport.width;
			canvas.style.height = viewport.height + "px";
			canvas.style.width = viewport.width + "px";

			// Render PDF page into canvas context
			let renderContext = {
				canvasContext: context,
				viewport: viewport,
			};

			page.render(renderContext);
		});
	});
</script>

<div class="w-full h-full py-28 flex items-center justify-center">
	<div class="w-3/5 h-full p-8 bg-white shadow-md rounded-lg flex flex-col items-center">
		<span class="text-3xl">Regulamin</span>
		<br />
		<span class="text-sm mb-2">Przeczytaj uważnie</span>
		<div style="height: 90%" class=" flex justify-center overflow-auto border-4 rounded-md border-purple-500">
			<canvas bind:this={canvas} />
		</div>
	</div>
</div>
