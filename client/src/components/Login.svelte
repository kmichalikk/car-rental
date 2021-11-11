<script>
	let login = (e) => {
		console.log(e.target);
	};
</script>

<div class="w-screen h-screen flex items-center justify-center pb-32">
	<div class="sm:w-2/3 md:w-132 h-96 shadow-lg rounded-lg bg-white flex flex-col items-center p-4 xs:p-12">
		<form on:submit|preventDefault={login} class="flex flex-col h-full items-center justify-evenly">
			<h1 class="text-purple-700 text-4xl">Zaloguj się</h1>
			<input type="text" class="bg-purple-100 rounded-lg p-2" placeholder="Nick" />
			<input type="password" class="bg-purple-100 rounded-lg p-2" placeholder="Hasło" />
			<input
				type="submit"
				value="Loguj"
				class="bg-purple-700 text-white py-2 px-4 rounded-lg shadow-sm cursor-pointer"
			/>
		</form>
		<span>Nie masz jeszcze konta? <a href="/#/register" class="text-purple-700 font-semibold">Załóż je</a></span>
	</div>
</div>
