const path = require('path');

module.exports = {
    entry: {
        'settings': './assets/src/js/settings.js',
        'md-page-meta-box': './assets/src/js/md-page-meta-box.js',
    },
    output: {
        path: path.resolve(__dirname, './assets/build/js'),
        filename: '[name].bundle.js',
    },
    mode: 'development',
    module: {
        rules: [
            {
                test: /\.js$/,
                exclude: /node_modules/,
                use: {
                    loader: 'babel-loader',
                    options: {
                        presets: [
                            ['@babel/preset-env', { targets: '> 0.1%' }]
                        ]
                    }
                }
            }
        ]
    }
};