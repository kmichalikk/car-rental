const path = require("path");
const HtmlWebpackPlugin = require("html-webpack-plugin");
const { sveltePreprocess } = require("svelte-preprocess/dist/autoProcess");


module.exports = {
	entry: './src/index.js',
	output: {
		path: path.resolve(__dirname, "./dist"),
		filename: 'bundle.js'
	},
	module: {
		rules: [
			{
        test: /\.(html|svelte)$/,
				exclude: "/node_modules/",
        use: {
					loader: "svelte-loader",
					options: {
						preprocess: sveltePreprocess({
							postcss: true,
						})
					}
				}

      },
			{
				test: /\.css$/,
				use: ['style-loader', 'css-loader', 'postcss-loader']
			}
		]
	},
	resolve: {
    alias: {
      svelte: path.resolve('node_modules', 'svelte')
    },
    extensions: ['.mjs', '.js', '.svelte'],
    mainFields: ['svelte', 'browser', 'module', 'main']
  },
	plugins: [
		new HtmlWebpackPlugin({
			title: "Car Rental"
		})
	],
	mode: "development"
}