<script>
	import { user, serverTime } from "../stores";
	import { push } from "svelte-spa-router";
	import Card from "./Card.svelte";
	import Loading from "./Loading.svelte";
	import Button from "./Button.svelte";
	import { SERVER_URL } from "../config";

	export let params = {};
	let costPerHour = 0;
	let carID = 0;
	let userAgreed = false;
	$: getDetailsProm = new Promise((resolve, reject) => {
		let fd = new FormData();
		fd.append("target", "cardetails");
		fd.append("carid", params.carid);
		fetch(SERVER_URL, { method: "post", body: fd })
			.then((res) => res.json())
			.then((data) => {
				if (data.ok) {
					resolve(data.data);
					costPerHour = data.data.price;
					carID = data.data.id;
				} else {
					reject(data.data);
				}
			})
			.catch((err) => reject(err));
	});
	// update zakresu dat
	let minStartDate = "";
	let onDateFocus = () => {
		// dodajemy godzinę, żeby zaokrąglić w górę
		let date = new Date($serverTime);
		minStartDate = `${date.getFullYear()}-${date.getMonth() + 1}-${date.getDate()}T00:00`;
	};
	// update kosztów
	let startdtText;
	$: startTs = Date.parse(startdtText);
	let enddtText;
	$: endTs = Date.parse(enddtText);
	let totalCost = 0;
	let fixInputs = () => {
		// wyrównanie inputów do następnej pełnej godziny
		if (startTs) {
			if (startTs < $serverTime) startTs = $serverTime;
			let date = new Date(startTs + 3599000);
			startdtText = `${date.getFullYear()}-${("0" + (date.getMonth() + 1)).slice(-2)}-${("0" + date.getDate()).slice(
				-2
			)}T${("0" + date.getHours()).slice(-2)}:00:00`;
		}
		if (endTs) {
			if (endTs > startTs) {
				let date = new Date(endTs + 3599000);
				enddtText = `${date.getFullYear()}-${("0" + (date.getMonth() + 1)).slice(-2)}-${("0" + date.getDate()).slice(
					-2
				)}T${("0" + date.getHours()).slice(-2)}:00:00`;
			} else {
				enddtText = "";
			}
		}

		// obliczenie totalCost - jeśli to możliwe
		if (endTs < startTs) {
			enddtText = "";
			totalCost = 0;
		} else if (endTs && startTs) {
			let hours = Math.floor((endTs - startTs) / 3600000);
			if (hours < 1) {
				totalCost = 0;
			} else {
				totalCost = hours * costPerHour;
			}
		}
	};
	let submitFinished = false;
	let submitSuccessful = false;
	let handleSubmit = () => {
		let fd = new FormData();
		fd.append("target", "requestcar");
		fd.append("carid", carID);
		fd.append("startdatetime", startdtText.replace("T", " "));
		fd.append("enddatetime", enddtText.replace("T", " "));
		fetch(SERVER_URL, { method: "post", body: fd })
			.then((res) => res.json())
			.then((data) => {
				submitFinished = true;
				if (data.ok) {
					submitSuccessful = true;
				}
			});
	};
</script>

<main class="pt-24 pb-8 px-6 h-full flex justify-center overflow-y-auto">
	<div
		class="rounded-lg shadow-lg h-full w-full lg:w-2/3 xl:w-3/5 bg-white flex items-center justify-center flex-col overflow-y-auto"
	>
		{#await getDetailsProm}
			<div class="flex items-center justify-center"><Loading /></div>
		{:then data}
			{#if !submitFinished}
				<div class="flex flex-col h-full w-full">
					<div
						class="flex-grow p-4 flex flex-col-reverse md:flex-row items-center justify-end md:items-start md:justify-between"
					>
						<div class="flex flex-col">
							<span class="text-3xl text-center md:text-left">Rezerwacja samochodu</span>
							<br />
							{#if $user.loggedIn && $user.type == "user"}
								{#if !data.booked}
									<form on:submit|preventDefault={handleSubmit} class="px-4 flex flex-col">
										<label for class="text-lg">Określ, kiedy chciałbyś wypożyczyć ten samochód <sup>*</sup>:</label>
										<input
											required
											type="datetime-local"
											name="startdate"
											class="p-2 rounded-md border-2 border-purple-700"
											min={minStartDate}
											on:focus|preventDefault={onDateFocus}
											on:change|preventDefault={fixInputs}
											bind:value={startdtText}
										/>
										<label for class="text-lg">Określ, kiedy wypożyczenia ma się skończyć <sup>*</sup>:</label>
										<input
											required
											type="datetime-local"
											name="enddate"
											class="p-2 rounded-md border-2 border-purple-700"
											min={minStartDate}
											on:focus|preventDefault={onDateFocus}
											on:change|preventDefault={fixInputs}
											bind:value={enddtText}
										/>
										<div class="mt-4 text-lg">
											<span class=" text-purple-700 font-bold">Koszt całkowity:</span>
											<span> {totalCost} zł</span>
										</div>
										<br />
										<div>
											<input type="checkbox" name="agreement" required bind:checked={userAgreed} />
											<label for="agreement">
												Przeczytałem i zgadzam się z
												<span
													class="text-purple-700 font-bold cursor-pointer"
													on:click={() => {
														push("/agreement");
													}}>regulaminem</span
												>
											</label>
										</div>
										<input
											type="submit"
											value="Rejestruję się"
											class="{totalCost > 0 && userAgreed
												? 'bg-purple-700 cursor-pointer'
												: 'bg-gray-500'} p-2 w-2/5 mt-2 rounded-md shadow-md text-white text-lg"
										/>
									</form>
								{:else}
									<div class="w-2/3 text-lg">Ten samochód jest zajęty.</div>
								{/if}
							{:else if $user.loggedIn && $user.type == "admin"}
								<div class="w-2/3 text-lg">Jako administrator nie możesz rezerwować samochodów.</div>
							{:else}
								<div class="w-2/3 text-lg">Rezerwacja samochodu jest możliwa jedynie po zalogowaniu.</div>
							{/if}
						</div>
						<div class="hidden md:block md:w-72 md:h-96 md:mb-4">
							<Card
								id={data.id}
								url={data.url}
								make={data.make}
								model={data.model}
								color={data.color}
								body={data.body}
								drive={data.drive}
								power={data.power}
								price={data.price}
								wantedBy={data.requestCount}
								booked={data.booked}
							/>
						</div>
						<span class="text-3xl md:hidden pb-8 ">{data.make} {data.model}</span>
					</div>
					{#if $user.loggedIn && $user.type == "user" && !data.booked}
						<div class="w-full text-gray-400 p-4">
							<sup>*</sup> Faktyczny przedział czasowy, na który zostanie wypożyczony samochód może ulec zmianie. Twoje zgłoszenie
							może zostać anulowane, jeśli więcej klientów będzie ubiegać się o ten samochód w tym samym czasie
						</div>
					{/if}
				</div>
			{:else if submitSuccessful}
				<i class="text-purple-700 fa-10x far fa-smile" />
				<span class="text-3xl my-8">Zgłoszenie przyjęte</span>
				<Button
					text="Wróć do strony głównej"
					clickFn={() => {
						push("/");
					}}
				/>
			{:else}
				<i class="text-purple-700 fa-10x far fa-frown" />
				<span class="text-3xl my-8">Nie udało się przesłać zgłoszenia</span>
				<Button
					text="Wróć do strony głównej"
					clickFn={() => {
						push("/");
					}}
				/>
			{/if}
		{/await}
	</div>
</main>
