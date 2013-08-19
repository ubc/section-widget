module.exports = function(grunt) {

  grunt.initConfig({
    pkg: grunt.file.readJSON('package.json'),
  	
    uglify: {
      options: {
        banner: '/*! <%= pkg.name %> <%= grunt.template.today("dd-mm-yyyy") %> */\n'
      },
      dist: {
        files: {
          'js/section-widget-admin.min.js': ['js/section-widget-admin.js'],
          'js/section-widget-tabs.min.js': ['js/section-widget-tabs.js'],
          'js/section-widget.min.js': ['js/section-widget.js'],
          'olt-checklist/olt-checklist.js': ['olt-checklist/olt-checklist.dev.js']
        }
      }
    },
    cssmin: {
	  add_banner: {
	    options: {
	      banner: '/*! <%= pkg.name %> <%= grunt.template.today("dd-mm-yyyy") %> */\n'
	    },
	    files: {
	      'css/section-widget-admin.min.css': ['css/section-widget-admin.css'],
	       'css/section-widget.min.css': ['css/section-widget.css']
	    }
	  }
	}
  });

  grunt.loadNpmTasks('grunt-contrib-uglify');
  grunt.loadNpmTasks('grunt-contrib-cssmin');

  grunt.registerTask('default', ['uglify', 'cssmin'] );
  
  /*
   grunt.registerTask('default', 'Log some stuff.', function() {
    grunt.log.write('Logging some stuff...').ok();
  });
  */

};