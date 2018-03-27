const path = require('path');

const outputPath = path.resolve(__dirname, 'public/js');

let webpackOptions = {
    entry: {
        homepageFunctions: 'public/js/homepageFunctions.js',
        progressLoaderFunctions: 'public/js/progressLoaderFunctions.js',
        scheduleViewerFunctions: 'public/js/scheduleViewerFunctions.js',
    },
    output: {
        filename: '[name].min.js',
        path: outputPath
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
                                ['env', {
                                    targets: {
                                        browsers: ["last 2 versions", "safari >= 9"]
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
    stats: {
        colors: true
    },
    devtool: "source-map"
};

module.exports = webpackOptions;
