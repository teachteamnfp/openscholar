Use the following command to compile SASS files to CSS:
compass compile [path/to/config.rb] [--environment development|production]


The _colors.sass contains color variables and it needs to be imported
at the top of the theme specific SASS files as follows:
@import "colors"


Theme specific file needs to be named as follows:
school-theme_style.sass