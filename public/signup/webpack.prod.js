const path = require("path");
const common = require("./webpack.common");
const merge = require("webpack-merge");
const { CleanWebpackPlugin } = require("clean-webpack-plugin");
const OptimizeCssAssetsPlugin = require("optimize-css-assets-webpack-plugin");
const TerserPlugin = require("terser-webpack-plugin");

module.exports = merge(common, {
  mode: "production",
  output: {
    filename: "[name].js",
    path: path.resolve(__dirname, "js"),
  },
  optimization: {
    minimizer: [new OptimizeCssAssetsPlugin(), new TerserPlugin()],
  },
  plugins: [new CleanWebpackPlugin()],
  module: {
    rules: [
      {
        test: /\.scss$/,
        use: [
          "style-loader", //4. Inject styles into DOM
          "css-loader", // 3. Turns css into commonjs
          {
            loader: "postcss-loader", // 2. Add Autoprefixer to CSS
            options: {
              ident: "postcss",
              plugins: [require("autoprefixer")],
            },
          },
          "sass-loader", // 1. Turns sass into css
        ],
      },
    ],
  },
});
