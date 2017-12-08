# ABOUT SASS AND COMPASS

Sass is a language that is just normal CSS plus some extra features, like
variables, nested rules, math, mixins, etc. If your stylesheets are written in
Sass, helper applications can convert them to standard CSS so that you can
include the CSS in the normal ways with your theme.

To learn more about Sass, visit: http://sass-lang.com

Compass is a helper library for Sass. It includes libraries of shared mixins, a
package manager to add additional extension libraries, and an executable that
can easily convert Sass files into CSS.

To learn more about Compass, visit: http://compass-style.org


# INSTALLING RUBY

You can use several tools to install Ruby. This page describes how to use major
package management systems and third-party tools for managing and installing Ruby
and how to build Ruby from source.

To learn how to install Sass, visit: https://www.ruby-lang.org/en/documentation/installation


# INSTALLING SASS

Sass and Compass get installed as Ruby gems so you'll need to have Ruby on your machine.
You can install sass on your machine by running following commands:

```bash
gem update --system
gem install sass
```

To learn how to install Sass, visit: http://sass-lang.com/install


# INSTALLING COMPASS

Sass and Compass get installed as Ruby gems so you'll need to have Ruby on your machine.
You can install compass on your machine by running following commands:

```bash
gem update --system
gem install compass
```

To learn how to install Compass, visit: http://compass-style.org/install/


# USING `os_style_override` MODULE

Directory structure for the `Harvard school`, `hwpi_modern` and `hwpi_vibrant` theme:

```
os_style_override
|-- README.txt
|-- config.rb
|-- os_style_override.info
|-- os_style_override.module
|-- sass
    |-- _colors.sass
    |-- harvard-hwpi_modern.sass
    |-- harvard-hwpi_vibrant.sass
```

The `_colors.sass` contains color variables and it needs to be imported
at the top of the theme specific SASS files as follows:

```css
@import "colors"
```

The best approach is to compile SASS files on local or development environment.

## OPENSCHOLAR BUILD PROCESS

Following steps will help you compile SASS files to CSS.

```bash
cd os_style_override
compass compile --environment production
```

Now, you just need to run the openscholar build script.

## SITE THAT IS LIVE

Following steps will help you compile SASS files to CSS.

```bash
cd os_style_override
compass compile --environment production
cd ..
tar -zcvf os_style_override.tar.gz os_style_override
```

Now you can upload it to the production server and extract it inside the related school module directory (e.g. harvard).

```bash
cd [OPEN_SCHOLAR_ROOT]/profiles/openscholar/modules/[SCHOOL_DIRECTORY]/
tar -zxvf os_style_override.tar.gz
```

Now you should have a `os_style_override` directory in the related school module. For safety reasons, you can remove the `os_style_override.tar.gz` file or move it to backup directory.


