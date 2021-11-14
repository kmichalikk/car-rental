<script>
	import { filters } from "../stores";

	let width = 0;
	let height = 0;
	let collapsed = true;
	let showOpts = () => {
		if (collapsed) {
			width = 300;
			height = 500;
			collapsed = false;
		} else {
			width = 0;
			height = 0;
			collapsed = true;
		}
	};
</script>

<div
	class="absolute top-24 left-6 shadow-lg rounded-lg bg-white overflow-visible"
	style="width: {width}px; height: {height}px;"
>
	<div
		class="absolute top-2 left-2 w-12 h-12 flex items-center justify-center shadow-lg rounded-lg cursor-pointer {collapsed
			? 'bg-white'
			: 'bg-purple-700'}"
		on:click={showOpts}
	>
		<i class="fa fa-sliders-h text-lg {collapsed ? 'text-purple-700' : 'text-white'}" />
	</div>
	{#if !collapsed}
		<div class="ml-20 mr-2 h-16 flex items-center text-2xl select-none justify-between">
			<span>Filtry</span>
			<span
				class="text-base text-purple-700 cursor-pointer"
				on:click={() => {
					filters.update((up) => {
						up.numFilters = 0;
						for (let i in up.makes) up.makes[i].selected = false;
						for (let i in up.colors) up.colors[i].selected = false;
						for (let i in up.bodies) up.bodies[i].selected = false;
						for (let i in up.drives) up.drives[i].selected = false;
						return up;
					});
				}}>Usuń wszystkie</span
			>
		</div>
		<div class=" px-4 py-2 overflow-auto h-100 select-none">
			<div class="text-xl mt-2">Marki</div>
			<hr />
			{#each $filters.makes as make, i}
				<span>
					<input
						type="checkbox"
						bind:checked={$filters.makes[i].selected}
						on:click={() => {
							filters.update((up) => {
								// nowy stan checkboxa
								let newState = !$filters.makes[i].selected;
								up.makes[i].selected = newState;
								// inkrementujemy liczbę filtrów, jeśli true
								if (newState) up.numFilters++;
								else up.numFilters--;
								return up;
							});
						}}
					/>
					{make.name}
				</span><br />
			{/each}
			<div class="text-xl mt-2">Kolory</div>
			<hr />
			{#each $filters.colors as color, i}
				<span>
					<input
						type="checkbox"
						bind:checked={$filters.colors[i].selected}
						on:click={() => {
							filters.update((up) => {
								// nowy stan checkboxa
								let newState = !$filters.colors[i].selected;
								up.colors[i].selected = newState;
								// inkrementujemy liczbę filtrów, jeśli true
								if (newState) up.numFilters++;
								else up.numFilters--;
								return up;
							});
						}}
					/>
					{color.name}
				</span><br />
			{/each}
			<div class="text-xl mt-2">Typy nadwozia</div>
			<hr />
			{#each $filters.bodies as body, i}
				<span>
					<input
						type="checkbox"
						bind:checked={$filters.bodies[i].selected}
						on:click={() => {
							filters.update((up) => {
								// nowy stan checkboxa
								let newState = !$filters.bodies[i].selected;
								up.bodies[i].selected = newState;
								// inkrementujemy liczbę filtrów, jeśli true
								if (newState) up.numFilters++;
								else up.numFilters--;
								return up;
							});
						}}
					/>
					{body.name}
				</span><br />
			{/each}
			<div class="text-xl mt-2">Typy napędu</div>
			<hr />
			{#each $filters.drives as drive, i}
				<span>
					<input
						type="checkbox"
						bind:checked={$filters.drives[i].selected}
						on:click={() => {
							filters.update((up) => {
								// nowy stan checkboxa
								let newState = !$filters.drives[i].selected;
								up.drives[i].selected = newState;
								// inkrementujemy liczbę filtrów, jeśli true
								if (newState) up.numFilters++;
								else up.numFilters--;
								return up;
							});
						}}
					/>
					{drive.name}
				</span><br />
			{/each}
		</div>
	{:else if $filters.numFilters > 0}
		<div
			class="absolute left-10 w-6 h-6 rounded-xl bg-white shadow-lg flex items-center justify-center font-bold select-none"
		>
			{$filters.numFilters < 100 ? $filters.numFilters : "#"}
		</div>
	{/if}
</div>
