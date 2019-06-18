const path = require("path");

const outputPath = path.resolve(__dirname, "./src/public/js");
const { CleanWebpackPlugin } = require("clean-webpack-plugin");
const webpack = require("webpack");

let webpackOptions = {
    entry: {
        homepageFunctions: "./src/public/js/homepageFunctions.js",
        progressLoaderFunctions: "./src/public/js/progressLoaderFunctions.js",
        scheduleViewerFunctions: "./src/public/js/scheduleViewerFunctions.js"
    },
    output: {
        filename: "[name]-[hash].min.js",
        path: outputPath,
        publicPath: "public/js/"
    },
    module: {
        rules: [
            {
                test: /\.js$/,
                exclude: /(node_modules|bower_components)/,
                use: [
                    {
                        loader: "babel-loader",
                        options: {
                            presets: [
                                [
                                    "es2015",
                                    {
                                        targets: {
                                            browsers: ["last 2 versions", "safari >= 9", "ie >= 10"]
                                        },
                                        useBuiltIns: true
                                    }
                                ]
                            ]
                        }
                    }
                ]
            }
        ]
    },
    plugins: [
        new CleanWebpackPlugin({
            cleanOnceBeforeBuildPatterns: [outputPath + "/*Functions-*.min.js*"]
        })
    ],
    stats: {
        colors: true
    },
    devtool: "source-map"
};

module.exports = webpackOptions;
