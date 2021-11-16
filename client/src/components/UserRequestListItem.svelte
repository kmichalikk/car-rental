<script>
	import { serverTime } from "../stores";
	export let reqid = 0;
	export let user = "";
	export let prefStart = "";
	export let prefEnd = "";
	export let submitFunc = (reqid, start, end) => {};

	let start = prefStart;
	let end = prefEnd;

	// update inputów z datetime
	let minStartDate = "";
	let onDateFocus = () => {
		// dodajemy godzinę, żeby zaokrąglić w górę
		let date = new Date($serverTime);
		minStartDate = `${date.getFullYear()}-${date.getMonth() + 1}-${date.getDate()}T00:00`;
	};
	$: startTs = Date.parse(start);
	$: endTs = Date.parse(end);
	let fixInputs = () => {
		// wyrównanie inputów do następnej pełnej godziny
		if (startTs) {
			if (startTs < $serverTime) startTs = $serverTime;
			let date = new Date(startTs + 3599000);
			start = `${date.getFullYear()}-${("0" + (date.getMonth() + 1)).slice(-2)}-${("0" + date.getDate()).slice(-2)}T${(
				"0" + date.getHours()
			).slice(-2)}:00:00`;
		}
		if (endTs) {
			if (endTs > startTs) {
				let date = new Date(endTs + 3599000);
				end = `${date.getFullYear()}-${("0" + (date.getMonth() + 1)).slice(-2)}-${("0" + date.getDate()).slice(-2)}T${(
					"0" + date.getHours()
				).slice(-2)}:00:00`;
			} else {
				end = "";
			}
		}
	};
</script>

<form
	class="bg-purple-50 rounded-lg my-1 p-1 flex flex-col items-start xl:flex-row xl:items-center relative"
	on:submit|preventDefault={() => submitFunc(reqid, start, end)}
>
	<div class="self-start">
		<span class="font-bold text-purple-700 ml-2 mr-1 w-28">użytkownik:</span>
		{user}
	</div>
	<div class="self-start">
		<span class="font-bold text-purple-700 ml-2 mr-1">początek:</span>
		<input
			class="bg-purple-50"
			type="datetime-local"
			name="start"
			on:focus={onDateFocus}
			on:change={fixInputs}
			bind:value={start}
			min={minStartDate}
		/>
	</div>
	<div class="self-start">
		<span class="font-bold text-purple-700 ml-2 mr-1">koniec:</span>
		<input
			class="bg-purple-50"
			type="datetime-local"
			name="start"
			on:focus={onDateFocus}
			on:change={fixInputs}
			bind:value={end}
			min={minStartDate}
		/>
	</div>
	<i
		class="text-lg text-gray-300 hover:text-purple-700 fas fa-eraser absolute right-8 cursor-pointer"
		on:click={() => {
			start = prefStart;
			end = prefEnd;
		}}
	/>
	<button class="absolute right-2 cursor-pointer" type="submit">
		<i class="text-lg text-gray-300 hover:text-purple-700 fas fa-check-square" />
	</button>
</form>
