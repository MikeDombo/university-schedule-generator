const path = require('path');

const outputPath = path.resolve(__dirname, './src/public/js');
const CleanWebpackPlugin = require('clean-webpack-plugin'); //installed via npm
const webpack = require('webpack');
const PolyfillInjectorPlugin = require('webpack-polyfill-injector');

let webpackOptions = {
    entry: {
        homepageFunctions: `webpack-polyfill-injector?${JSON.stringify({
            modules: ['./src/public/js/homepageFunctions.js']
        })}!`,
        progressLoaderFunctions: `webpack-polyfill-injector?${JSON.stringify({
            modules: ['./src/public/js/progressLoaderFunctions.js']
        })}!`,
        scheduleViewerFunctions: `webpack-polyfill-injector?${JSON.stringify({
            modules: ['./src/public/js/scheduleViewerFunctions.js']
        })}!`,
    },
    output: {
        filename: '[name]-[hash].min.js',
        path: outputPath,
        publicPath: 'public/js/'
    },
    module: {
        rules: [
            {
                test: /\.js$/,
                exclude: /(node_modules|bower_components)/,
                use: [
                    {
                        loader: 'babel-loader',
                        options: {
                            presets: [
                                ['es2015', {
                                    targets: {
                                        browsers: ["last 2 versions", "safari >= 9", "ie >= 10"]
                                    },
                                    "useBuiltIns": true
                                }]
                            ]
                        }
                    }
                ]
            }
        ]
    },
    plugins: [
        new CleanWebpackPlugin([outputPath+"/*Functions-*.min.js*", outputPath+"/polyfills-*.min.*"]),
        new PolyfillInjectorPlugin({
            polyfills: ['String.prototype.includes']
        })
    ],
    stats: {
        colors: true
    },
    devtool: "source-map"
};

module.exports = webpackOptions;
