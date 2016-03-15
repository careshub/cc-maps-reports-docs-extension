'use strict';
module.exports = function(grunt) {

	// load all grunt tasks matching the `grunt-*` pattern
	require('load-grunt-tasks')(grunt);

	grunt.initConfig({
		pkg: grunt.file.readJSON('package.json'),

		// Watch for changes and trigger less, jshint, uglify and livereload
		watch: {
			options: {
				livereload: true
			},
			scripts: {
				files: ['public/js/*.js'],
				tasks: ['jshint', 'uglify']
			},
			styles: {
				files: ['public/css/*.less'],
				tasks: ['less:cleancss', 'postcss']
			}
		},

		less: {
			cleancss: {
				files: {
					'public/css/cc-mrad-public.css': 'public/css/cc-mrad-public.less'
				}
			}
		},

		postcss: {
		  options: {
			map: {
				inline: false, // save all sourcemaps as separate files...
				annotation: 'public/css/' // ...to the specified directory
			},
		    processors: [
		      require('autoprefixer')(),
		      require('cssnano')
		    ]
		  },
		  dist: {
		    src: 'public/css/*.css'
		  }
		},

		// JavaScript linting with jshint
		jshint: {
			all: [
				'public/js/*.js'
				]
		},

		// Uglify to concat, minify, and make source maps
		uglify: {
			options: {
				banner: '/*! <%= pkg.name %> - v<%= pkg.version %> - ' +
						'<%= grunt.template.today("yyyy-mm-dd") %> */'
			},
			common: {
				files: {
					'public/js/cc-mrad-public.min.js': ['public/js/cc-mrad-public.js']
				}
			}
		},

		// Image optimization
		imagemin: {
			dist: {
				options: {
					optimizationLevel: 7,
					progressive: true,
					interlaced: true
				},
				files: [{
					expand: true,
					cwd: 'public/img/',
					src: ['**/*.{png,jpg,gif}'],
					dest: 'public/img/'
				}]
			}
		}

	});

	// Register tasks
	// Typical run, cleans up css and js, starts a watch task.
	grunt.registerTask('default', ['less:cleancss', 'postcss', 'jshint', 'uglify:common', 'watch']);

	// Before releasing a build, do above plus minimize all images.
	grunt.registerTask('build', ['less:cleancss', 'postcss',  'jshint', 'uglify:common', 'imagemin']);

};