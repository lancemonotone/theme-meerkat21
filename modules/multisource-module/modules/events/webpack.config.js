// Return array of configurations
module.exports = function () {
  return exportModules( [js, css] );
};

// Config for JS files
const js = {
  entry: {
    'frontend.js': `${__dirname}/src/js/frontend.es6.js`,
    //'settings.js' : `${__dirname}/src/js/settings.es6.js`,
  },
  output: {
    path: `${__dirname}/js`,
    filename: '[name]'
  },
  module: {
    rules: [
      { test: /\.js$/, use: { loader: 'babel-loader' }, exclude: /node_modules/ },
    ]
  }
};

// Config for SCSS files
const css = {
  entry: {
    'frontend.css': `${__dirname}/src/scss/frontend.scss`,
    //'frontend.responsive.css': `${__dirname}/src/scss/frontend.responsive.scss`
  },
  output: {
    path: `${__dirname}/css`,
    filename: '[name]'
  },
  module: {
    rules: [
      {
        test: /\.scss$/,
        exclude: /node_modules/,
        // module chain executes from last to first
        use: [
          {
            loader: 'file-loader',
            options: { name: '[name].css', outputPath: '../css/' }
          },
          { loader: "remove-comments-loader" },
          { loader: 'extract-loader' },
          { loader: 'css-loader', options: { url: false, sourceMap: false } },
          { loader: 'resolve-url-loader' },
          { loader: 'sass-loader', options: { sourceMap: false } }
        ]
      },
    ]
  }
};

/**
 * Merge filetype configs with shared config and return them as an array of objects.
 * @param objs
 * @return {Array}
 */
const exportModules = objs => {
  const objArr = [];
  for ( let i = 0; i < objs.length; i++ ) {
    objArr.push( {
      ...config(),
      ...objs[i]
    } );
  }
  return objArr;
};

// Shared config options
const config = function () {
  return {
    mode: 'production',
    stats: {
      colors: true,
      children: false
    }
  }
};
