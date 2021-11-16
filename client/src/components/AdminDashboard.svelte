<script>
	import { user, serverTime, serverTimeNeedsUpdate } from "../stores.js";
	import { push } from "svelte-spa-router";
	import Button from "./Button.svelte";
	import Loading from "./Loading.svelte";
	import UserRequestListItem from "./UserRequestListItem.svelte";
	import { SERVER_URL } from "../config";

	//############
	//### requesty
	//############

	// pobranie requestów
	let userRequests = {};
	let updating = true;
	let updateRequests = () => {
		updating = true;
		let fd = new FormData();
		fd.append("target", "getrequests");
		fetch(SERVER_URL, { method: "post", body: fd })
			.then((res) => res.json())
			.then((data) => {
				updating = false;
				if (data.ok) {
					userRequests = {};
					for (let req of data.data) {
						if (!userRequests.hasOwnProperty(`${req.carMake} ${req.carModel} (id ${req.carID})`))
							userRequests[`${req.carMake} ${req.carModel} (id ${req.carID})`] = [];
						userRequests[`${req.carMake} ${req.carModel} (id ${req.carID})`].push(req);
					}
				}
			});
	};
	updateRequests();

	let acceptRequest = (reqid, start, end) => {
		console.log(reqid, start, end);
		let fd = new FormData();
		fd.append("target", "acceptrequest");
		fd.append("reqid", reqid);
		fd.append("startdatetime", start.replace("T", " "));
		fd.append("enddatetime", end.replace("T", " "));
		fetch(SERVER_URL, { method: "post", body: fd })
			.then((res) => res.json())
			.then((data) => {
				if (data.ok) {
					updateRequests();
				}
			});
	};

	//#######################
	//### zarządzanie userami
	//#######################

	let users = [];
	let updateUsers = () => {
		let fd = new FormData();
		fd.append("target", "getusers");
		fetch(SERVER_URL, { method: "post", body: fd })
			.then((res) => res.json())
			.then((data) => {
				if (data.ok) users = data.data;
			});
	};

	let grantAdmin = (userID) => {
		let fd = new FormData();
		fd.append("target", "grantadmin");
		fd.append("userid", userID);
		fetch(SERVER_URL, { method: "post", body: fd })
			.then((res) => res.json())
			.then((data) => {
				if (data.ok) {
					updateUsers();
				}
			});
	};

	//########################
	//### update czasu serwera
	//########################

	let newDateTime;
	let updateTime = () => {
		let date = new Date(newDateTime);
		let newTime = `${date.getFullYear()}-${date.getMonth() + 1}-${date.getDate()} ${("0" + date.getHours()).slice(
			-2
		)}:00:00`;
		let fd = new FormData();
		fd.append("target", "updateservertime");
		fd.append("datetime", newTime);
		fetch(SERVER_URL, { method: "post", body: fd })
			.then((res) => res.json())
			.then((data) => {
				if (data.ok) serverTimeNeedsUpdate.set({ needsUpdate: true });
			});
	};
	let fixInputs = () => {
		// wyrównanie inputów do następnej pełnej godziny
		newDateTime = newDateTime.slice(0, -2) + "00";
	};

	//##########################
	//### zwlekający użytkownicy
	//##########################

	let lateUsers = [];
	let updateLateUsers = () => {
		let fd = new FormData();
		fd.append("target", "getlate");
		fetch(SERVER_URL, { method: "post", body: fd })
			.then((res) => res.json())
			.then((data) => {
				if (data.ok) {
					lateUsers = data.data;
				}
			});
	};

	let forceReturnCar = (bookid) => {
		let fd = new FormData();
		fd.append("target", "returncar");
		fd.append("bookid", bookid);
		fetch(SERVER_URL, { method: "post", body: fd })
			.then((res) => res.json())
			.then((data) => {
				if (data.ok) updateLateUsers();
			});
	};

	let blockUser = (userid) => {
		let fd = new FormData();
		fd.append("target", "blockuser");
		fd.append("userid", userid);
		fetch(SERVER_URL, { method: "post", body: fd })
			.then((res) => res.json())
			.then((data) => {
				if (data.ok) updateLateUsers();
			});
	};

	//###########################
	//### zablokowani użytkownicy
	//###########################

	let blockedUsers = [];
	let updateBlockedUsers = () => {
		let fd = new FormData();
		fd.append("target", "getblocked");
		fetch(SERVER_URL, { method: "post", body: fd })
			.then((res) => res.json())
			.then((data) => {
				if (data.ok) blockedUsers = data.data;
			});
	};

	let unblockUser = (userid) => {
		let fd = new FormData();
		fd.append("target", "unblockuser");
		fd.append("userid", userid);
		fetch(SERVER_URL, { method: "post", body: fd })
			.then((res) => res.json())
			.then((data) => {
				if (data.ok) updateBlockedUsers();
			});
	};

	let tabs = [
		{ name: "requests", prettyName: "Zgłoszenia", cmd: updateRequests },
		{ name: "users", prettyName: "Użytkownicy", cmd: updateUsers },
		{
			name: "blocked",
			prettyName: "Blokowani",
			cmd: () => {
				updateBlockedUsers();
			},
		},
		{ name: "deadlines", prettyName: "Przetrzymujący", cmd: updateLateUsers },
		{ name: "simtime", prettyName: "Czas serwera", cmd: () => {} },
	];
	let currTab = "requests";
</script>

<main class="pt-24 pb-8 px-6 h-full flex justify-center">
	<div class="rounded-lg shadow-lg h-full px-4 w-full lg:w-4/5 xl:w-3/4 bg-white flex flex-col overflow-y-hidden">
		<div class="w-full h-16 flex items-center justify-between">
			<span class="text-lg md:text-3xl">Dashboard <span class="hidden sm:inline"> - Panel administratora</span></span>
			<Button
				clickFn={() => {
					let fd = new FormData();
					fd.append("target", "logout");
					fetch(SERVER_URL, { method: "post", body: fd })
						.then((res) => res.json())
						.then((data) => {
							if (data.ok) {
								user.set({ loggedIn: false, nick: "" });
								push("/");
							}
						});
				}}
				text="Wyloguj"
			/>
		</div>
		<hr />
		<div class="flex my-2 flex-wrap">
			{#each tabs as tab}
				<div
					class="m-1 p-1 md:p-2 w-28 md:w-36 rounded-lg shadow-lg box-border flex items-center justify-center text-lg cursor-pointer flex-wrap
						{currTab == tab.name ? 'bg-purple-700 text-white' : 'bg-white border-2 border-purple-500 text-purple-700'}"
					on:click={() => {
						currTab = tab.name;
						tab.cmd();
					}}
				>
					{tab.prettyName}
				</div>
			{/each}
		</div>
		<hr />
		{#if currTab == "requests"}
			<span class="text-2xl">Prośby o wypożyczenie użytkowników</span>
			<br />
			<span class="text-sm"> System nie przyjmie wypożyczenia zaczynającego się przed aktualną godziną </span>
			{#if !updating}
				<div class="overflow-y-auto my-4">
					{#each Object.keys(userRequests) as key}
						<span class="text-lg">{key}</span>
						<hr />
						{#each userRequests[key] as req}
							<UserRequestListItem
								reqid={req.reqID}
								user={req.userNick}
								prefStart={req.preferredStart.replace(" ", "T")}
								prefEnd={req.preferredEnd.replace(" ", "T")}
								submitFunc={acceptRequest}
							/>
						{/each}
					{/each}
				</div>
			{:else}
				<div class="flex items-center justify-center flex-grow pb-48">
					<Loading color="#6d28d9" />
				</div>
			{/if}
		{:else if currTab == "users"}
			<span class="text-2xl">Użytkownicy</span>
			<br />
			<span class="text-sm">Możesz przyznać uprawnienia administratora użytkownikom standardowym</span>
			<div class="overflow-y-auto">
				{#each users as user}
					<div
						class="flex flex-col items-start bg-purple-50 hover:bg-purple-100 p-2 rounded-lg m-1 relative sm:flex-row sm:items-center"
					>
						<span class="px-2"><b class="font-bold text-purple-700">id:</b> {user.id}</span>
						<span class="px-2"><b class="font-bold text-purple-700">użytkownik:</b> {user.nick}</span>
						<span class="px-2"><b class="font-bold text-purple-700">email:</b> {user.email}</span>
						<i
							class="fas fa-angle-double-up text-xl text-gray-300 hover:text-purple-700 cursor-pointer absolute right-2"
							on:click={() => grantAdmin(user.id)}
						/>
					</div>
				{/each}
			</div>
		{:else if currTab == "blocked"}
			<span class="text-2xl">Zablokowani użytkownicy</span>
			<br />
			<span class="text-sm mb-2">Możesz odblokować zablokowanych użytkowników</span>
			<div class="overflow-y-auto">
				{#each blockedUsers as user}
					<div
						class="flex flex-col items-start bg-purple-50 hover:bg-purple-100 p-2 rounded-lg m-1 relative md:flex-row md:items-center"
					>
						<span class="px-2"><b class="font-bold text-purple-700">użytkownik:</b> {user.nick}({user.id})</span>
						<span class="px-2"><b class="font-bold text-purple-700">email:</b> {user.email}</span>
						<i
							class="fas fa-unlock-alt text-lg text-gray-300 hover:text-purple-700 absolute right-2 cursor-pointer"
							on:click={() => {
								unblockUser(user.id);
							}}
						/>
					</div>
				{/each}
			</div>
		{:else if currTab == "deadlines"}
			<span class="text-2xl">Spóźnieni z oddaniem</span>
			<br />
			<span class="text-sm mb-2"
				>Możesz zabrać samochód użytkownikowi, który nie oddał go w wyznaczonym terminie lub zablokować tego użytkownika
				- wtedy zabierane są mu wszystkie samochody i nie może się logować</span
			>
			<div class="overflow-y-auto">
				{#each lateUsers as user}
					<div
						class="flex flex-col items-start bg-purple-50 hover:bg-purple-100 p-2 rounded-lg m-1 relative md:flex-row md:items-center"
					>
						<span class="px-2"><b class="font-bold text-purple-700">użytkownik:</b> {user.nick}({user.userid})</span>
						<span class="px-2"><b class="font-bold text-purple-700">auto:</b> {user.make} {user.model}</span>
						<span class="px-2"><b class="font-bold text-purple-700">termin:</b> {user.enddate}</span>
						<i
							class="fas fa-undo-alt text-lg text-gray-300 hover:text-purple-700 absolute right-2 cursor-pointer"
							on:click={() => forceReturnCar(user.bookid)}
						/>
						<i
							class="fas fa-user-lock text-lg text-gray-300 hover:text-purple-700 absolute right-8 cursor-pointer"
							on:click={() => blockUser(user.userid)}
						/>
					</div>
				{/each}
			</div>
		{:else if currTab == "simtime"}
			<div class="block">
				<span class="text-2xl">Czas serwera</span>
				<br /><br />
				<span class="text-sm">Możesz ustawić tutaj czas serwera</span>
				<br />
				<input type="datetime-local" class="m-4" on:change={fixInputs} bind:value={newDateTime} />
				<br />
				<Button text="Potwierdź" clickFn={updateTime} />
			</div>
		{/if}
	</div>
</main>
