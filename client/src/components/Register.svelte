<script>
	import Loading from "./Loading.svelte";
	let nick = "";
	$: nickValid = nick;
	let email = "";
	$: emailValid = email.match(/[\w\.-]+@[\w\.-]+\.[\w]+/);
	let pass = "";
	$: passValid =
		pass.match(/.*[a-z].*/) &&
		pass.match(/.*[0-9].*/) &&
		pass.match(/.*[A-Z].*/) &&
		pass.match(/.*[`~!@#$%^&*()\-_=+;:'"\[\]\{\}\\|,\.<>/?].*/) &&
		pass.length >= 8;
	let passValidate = "";
	$: passRetypeValid = passValid && pass == passValidate;

	let sent = false;
	let gotResponse = false;
	let success = false;
	let register = () => {
		if (nickValid && emailValid && passValid && passRetypeValid) {
			sent = true;
			let fd = new FormData();
			fd.append("nick", nick);
			fd.append("email", email);
			fd.append("password", pass);
			fd.append("type", "user");
			fd.append("target", "register");
			fetch("http://localhost:8080/carRental/server/server.php", { method: "post", body: fd })
				.then((res) => res.json())
				.then((data) => {
					gotResponse = true;
					if (data.ok) success = true;
					else success = false;
				})
				.catch((err) => {
					gotResponse = true;
					success = false;
				});
		}
	};
</script>

<div class="w-screen h-screen flex items-center justify-center pb-32">
	<div class="sm:w-2/3 md:w-132 h-108 shadow-lg rounded-lg bg-white flex flex-col items-center p-4 xs:p-12">
		{#if !sent}
			<form on:submit|preventDefault={register} class="flex flex-col h-full w-full items-center justify-evenly">
				<h1 class="text-purple-700 text-3xl md:text-4xl">Zarejestruj się</h1>
				<div class="bg-purple-100 rounded-lg p-2 w-56 flex items-center justify-between">
					<input type="text" class="bg-purple-100 outline-none w-40" placeholder="Nick" bind:value={nick} />
					{#if !nickValid}
						<i class="fas fa-exclamation-triangle" />
					{/if}
				</div>
				<div class="bg-purple-100 rounded-lg p-2 w-56 flex items-center justify-between">
					<input
						type="email"
						class="bg-purple-100 outline-none w-40"
						placeholder="email@email.com"
						bind:value={email}
					/>
					{#if !emailValid}
						<i class="fas fa-exclamation-triangle" />
					{/if}
				</div>
				<div class="bg-purple-100 rounded-lg p-2 w-56 flex items-center justify-between">
					<input type="password" class="bg-purple-100 outline-none w-40" placeholder="Hasło" bind:value={pass} />
					{#if !passValid}
						<i class="fas fa-exclamation-triangle" />
					{/if}
				</div>
				<div class="bg-purple-100 rounded-lg p-2 w-56 flex items-center justify-between">
					<input
						type="password"
						class="bg-purple-100 outline-none w-40"
						placeholder="Powtórz hasło"
						bind:value={passValidate}
					/>
					{#if !passRetypeValid}
						<i class="fas fa-exclamation-triangle" />
					{/if}
				</div>
				<input
					type="submit"
					value="Zarejestruj się"
					class="bg-purple-700 text-white py-2 px-4 rounded-lg shadow-sm cursor-pointer"
				/>
			</form>
			<span>Masz już konto? <a href="/#/login" class="text-purple-700 font-semibold">Zaloguj się</a></span>
		{:else if !gotResponse}
			<Loading />
		{:else if success}
			<div class="text-purple-700 text-4xl">Sukces</div>
			<div>teraz możesz się zalogować klikając <a href="/#/login" class="text-purple-700 font-semibold">tutaj</a></div>
		{:else}
			<div class="text-purple-700 text-4xl">Ups</div>
			<div class="text-center mt-4">
				Coś poszło nie tak<br />Możesz spróbować jeszcze raz klikając
				<span
					class="text-purple-700 font-semibold cursor-pointer"
					on:click={() => {
						nick = "";
						email = "";
						pass = "";
						passValidate = "";
						sent = false;
						gotResponse = false;
						success = false;
					}}>tutaj</span
				><br />
				Lub zalogować się klikając <a href="/#/login" class="text-purple-700 font-semibold">tutaj</a>
			</div>
		{/if}
	</div>
</div>
