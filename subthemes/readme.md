
- To build subtheme assets:


    1. Import `frontend.scss` into `frontend.js`
    2. cd into `subtheme/assets` directory
    3. Run `npm run start` to develop, or `npm run build` to compile and minify for deployment

- What is `build/frontend.asset.php`?  
  - The subtheme version is automatically updated in `wp_enqueue_script` each build
  - All imported js dependencies will be automatically added as `wp_enqueue_script` dependencies
  - See `welcome/assets/build/frontend.asset.php` for an example
  - [More info](https://github.com/WordPress/gutenberg/blob/fb3d84b4a8245cc25b5978eddf95e4a3ff8d7477/docs/how-to-guides/javascript/js-build-setup.md#dependency-management)
  

- See `lib/subtheme/class.subtheme.php` for the logic that glues all the assets in each subtheme together


- There's a ***Subthemes*** menu item in the dashboard sidebar for subtheme stats: 
  - `wp-admin/admin.php?page=m21_subthemes` 
