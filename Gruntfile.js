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
        imagemin: {
            plugin: {
                files: [
                    {
                        expand: true,
                        cwd: '<%= pkg.name %>/',
                        src: ['**/*.{png,jpg}'],
                        dest: '<%= pkg.name %>/'
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
        },
        compress: {
            plugin: {
                options: {
                    archive: 'release/<%= pkg.name %>-<%= pkg.version %>.zip',
                    mode: 'zip'
                },
                expand: true,
                cwd: '<%= pkg.name %>/',
                src: ['**'],
                dest: '<%= pkg.name %>/'
            }
        }
    });

    /* Load plugins */
    grunt.loadNpmTasks('grunt-contrib-cssmin');
    grunt.loadNpmTasks('grunt-contrib-uglify');
    grunt.loadNpmTasks('grunt-contrib-imagemin');
    grunt.loadNpmTasks('grunt-version');
    grunt.loadNpmTasks('grunt-contrib-compress');

    /* Create default task */
    grunt.registerTask('default', ['version', 'cssmin', 'uglify', 'imagemin']);

};
