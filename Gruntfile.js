module.exports = function(grunt) {

    /* Project configurations */
    grunt.initConfig({
        pkg: grunt.file.readJSON('package.json'),
        cssmin: {
            plugin: {
                files: [
                    {
                        expand: true,
                        cwd: '<%= pkg.name %>',
                        src: ['*.css', '!*.min.css'],
                        dest: '<%= pkg.name %>',
                        ext: '.min.css'
                    },
                    {
                        expand: true,
                        cwd: '<%= pkg.name %>/static/css',
                        src: ['*.css', '!*.min.css'],
                        dest: '<%= pkg.name %>/static/css',
                        ext: '.min.css'
                    }
                ]
            }
        },
        uglify: {
            plugin: {
                files: [
                    {
                        expand: true,
                        cwd: '<%= pkg.name %>/static/js',
                        src: ['*.js', '!*.min.js'],
                        dest: '<%= pkg.name %>/static/js',
                        ext: '.min.js'
                    }
                ]
            }
        },
        version: {
            project: {
                src: ['composer.json']
            },
            plugin: {
                options: {
                    prefix: 'Version:\\s+'
                },
                src: ['<%= pkg.name %>/<%= pkg.name %>.php']
            },
            loader: {
                options: {
                    prefix: 'const VERSION\\s+=\\s+[\'"]'
                },
                src: ['<%= pkg.name %>/src/Loader.php']
            },
            readme: {
                options: {
                    prefix: 'Stable tag:\\s+'
                },
                src: ['<%= pkg.name %>/readme.txt']
            }
        }
    });

    /* Load plugins */
    grunt.loadNpmTasks('grunt-contrib-cssmin');
    grunt.loadNpmTasks('grunt-contrib-uglify');
    grunt.loadNpmTasks('grunt-version');

    /* Create default task */
    grunt.registerTask('default', ['version', 'cssmin', 'uglify']);
};
