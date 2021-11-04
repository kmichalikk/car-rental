<script>
	export let color = "white";
</script>

<div class="w-36 h-15 inline-block" style="--color:{color}">
	<svg
		width="144"
		height="60"
		viewBox="0 0 24 10"
		preserveAspectRatio="xMidYMin"
	>
		<path
			style="fill:none;stroke-width:0.529167;stroke-linecap:round;stroke-linejoin:round;stroke-miterlimit:4;stroke-opacity:1"
			d="m 11.040951,0.9642675 h 4.929364 l 1.370144,2.480313 -5.259151,0.02128 -0.715303,-1.5955"
			id="car-window-front"
			sodipodi:nodetypes="ccccc"
		/>
		<path
			style="fill:none;stroke-width:0.529167;stroke-linecap:round;stroke-linejoin:round;stroke-miterlimit:4;stroke-opacity:1"
			d="m 12.443141,4.3007525 10.125672,0.01603 0.01452,2.810632 h -7.000465 v -1.880459"
			id="car-body-front"
			sodipodi:nodetypes="ccccc"
		/>
		<path
			style="fill:none;stroke-width:0.529167;stroke-linecap:round;stroke-linejoin:round;stroke-miterlimit:4;stroke-opacity:1"
			d="m 14.595872,7.1454285 -1.086248,-0.02453 c -0.276038,-0.834774 -0.732343,-1.322426 -1.434964,-1.339222 -0.889997,-0.01421 -1.366626,0.738872 -1.533753,1.354089 L 6.0631755,7.0989885 C 5.9042339,6.3547075 5.4412239,5.7668155 4.6317896,5.7622875 3.7123809,5.7584565 3.2952787,6.5324195 3.1785089,7.0894005 L 1.416667,7.1024405 1.441445,4.3167765 H 4.0402567 L 5.675074,0.9642685 h 4.389456"
			id="car-body-back"
			sodipodi:nodetypes="cccccccccccc"
		/>
		<path
			style="fill:none;stroke-width:0.529167;stroke-linecap:round;stroke-linejoin:round;stroke-miterlimit:4;stroke-opacity:1"
			d="m 5.0497958,4.3167805 1.2297234,-2.458266 h 1.4012646 l 0.0064,2.458267 H 6.0095495"
			id="car-window-1"
			sodipodi:nodetypes="ccccc"
		/>
		<path
			style="fill:none;stroke-width:0.529167;stroke-linecap:round;stroke-linejoin:round;stroke-miterlimit:4;stroke-opacity:1"
			d="m 8.5594839,4.3167805 -0.02112,-2.458266 h 1.7516231 l 1.061299,2.458267 H 9.4050519"
			id="car-window-2"
			sodipodi:nodetypes="ccccc"
		/>
		<path
			id="car-headlamp-1"
			style="fill:#cccccc;fill-opacity:0;stroke-width:0.529167;stroke-linecap:round;stroke-linejoin:round;stroke-miterlimit:4;"
			d="m 17.595814,5.6974695 a 0.55758486,0.55758486 0 0 1 -0.557586,0.557585 0.55758486,0.55758486 0 0 1 -0.557583,-0.557585 0.55758486,0.55758486 0 0 1 0.557583,-0.557585 0.55758486,0.55758486 0 0 1 0.557586,0.557585 z"
		/>
		<path
			id="car-headlamp-2"
			style="fill:#cccccc;fill-opacity:0;stroke-width:0.529167;stroke-linecap:round;stroke-linejoin:round;stroke-miterlimit:4;"
			d="m 21.694115,5.6974695 a 0.55758486,0.55758486 0 0 1 -0.557585,0.557585 0.55758486,0.55758486 0 0 1 -0.557583,-0.557585 0.55758486,0.55758486 0 0 1 0.557583,-0.557585 0.55758486,0.55758486 0 0 1 0.557585,0.557585 z"
		/>
		<path
			id="car-wheel-back"
			style="fill:#cccccc;fill-opacity:0;stroke-width:0.529167;stroke-linecap:round;stroke-linejoin:round;stroke-miterlimit:4;"
			d="M 5.3758788,7.8075935 A 0.7590323,1.2281402 0 0 1 4.6168466,9.0357325 0.7590323,1.2281402 0 0 1 3.8578141,7.8075935 0.7590323,1.2281402 0 0 1 4.6168466,6.5794515 0.7590323,1.2281402 0 0 1 5.3758788,7.8075935 Z"
		/>
		<path
			id="car-wheel-front"
			style="fill:#cccccc;fill-opacity:0;stroke-width:0.529167;stroke-linecap:round;stroke-linejoin:round;stroke-miterlimit:4;"
			d="m 12.798858,7.8075935 a 0.7590323,1.2281402 0 0 1 -0.759032,1.228139 0.7590323,1.2281402 0 0 1 -0.759033,-1.228139 0.7590323,1.2281402 0 0 1 0.759033,-1.228142 0.7590323,1.2281402 0 0 1 0.759032,1.228142 z"
		/>
	</svg>
</div>

<style>
	@keyframes animatePath {
		0% {
			stroke-dashoffset: var(--len);
		}
		80% {
			stroke-dashoffset: 0;
		}
	}

	path {
		stroke: var(--color);
	}

	#car-window-front {
		--len: 14.77;
		stroke-dasharray: var(--len);
		animation: animatePath 2s ease-in-out infinite;
	}
	#car-body-front {
		--len: 21.82;
		stroke-dasharray: var(--len);
		animation: animatePath 2s ease-in-out infinite;
	}
	#car-headlamp-1,
	#car-headlamp-2 {
		--len: 3.5;
		stroke-dasharray: var(--len);
		animation: animatePath 2s ease-in-out infinite;
	}
	#car-body-back {
		--len: 29.43;
		stroke-dasharray: var(--len);
		animation: animatePath 2s ease-out infinite;
	}
	#car-wheel-front,
	#car-wheel-back {
		--len: 6.33;
		stroke-dasharray: var(--len);
		animation: animatePath 2s ease-out infinite;
	}
	#car-window-1 {
		--len: 8.29;
		stroke-dasharray: var(--len);
		animation: animatePath 2s ease-out infinite;
	}
	#car-window-2 {
		--len: 8.83;
		stroke-dasharray: var(--len);
		animation: animatePath 2s ease-out infinite;
	}
</style>
