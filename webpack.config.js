const path = require('path');

module.exports = {
    resolve: {
        extensions: [ '.tsx', '.ts', '.js', '.vue' ],
        alias: {
            '@': path.resolve('resources/js'),
            // 'vue': '@vue/runtime-dom',
            'Vue': 'vue/dist/vue.esm-bundler.js',
            'vue$': 'vue/dist/vue.esm.js',
        },
    },
};
