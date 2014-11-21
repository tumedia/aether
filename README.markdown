# Aether
## A modular PHP framework

### aether.config.xml example

>        <config>
>            <site name="www.foo.com">
>                <urlRules>
>                    <!-- Matches http://www.foo.com/foo -->
>                    <rule name="foo">
>                        <module provides="header">Header</module>
>                        <template>Header.tpl</template>
>                        <option name="title">Foo.com/Foo</option>
>                    </rule>
>
>                    <!-- Index -->
>                    <rule match="">
>                        <template>Index.tpl</template>
>                    </rule>
>
>                    <!-- Everything that isn't catched with a <rule> is run here, e.g. 404's -->
>                    <rule default="true">
>                        <module>Status404</module>
>                        <template>404.tpl</template>
>                    </rule>
>                </urlRules>
>            </site>
>        </config>

### The config elements

* `<site>`
    * **Attributes**
        * _name_: (string) Name of the host. Wildcard support (*)

    * **Examples**

>            <site name="dev.foo.com>(...)</site>
>            <site name="*">(...)</site>

* `<rule>`
    * **Attributes**
        * _match_: (string) Regular string
        * _patterh_: (string) Regex URL matching
        * _store_: (string) Stores match in magic variable reachable from AetherConfig::getUrlVar(`$match`);

    * **Examples**

>            <rule match="foo" store="path">(...)</rule>
>            <rule pattern="/^(.+)$/" store="path">(...)</rule>

* `<template>`
    * **Value**: Name of the template file

    * **Examples**

>            <template>foo.tpl</template>
>            <template>dir/foo.tpl</template>

* `<import>`
    * **Value**: The name of the XML config file in /config to include

    * **Example**

>            <import>globals.xml</import>

* `<module>`
    * **Value**: The name of the module

    * **Attributes**
        * _provides_: (string) Stores module return data in the magic template array `$aether.providers`
        * _cache_: (int) Cachetime in seconds

    * **Examples**

>            <module provides="header" cache="60">Header</module>
>            <module provides="header" cache="60">dir/Header</module>
>            <module>Header</module>


### Magic options
* _searchpath_: Path(s) where Aether looks for modules and sections. Multipath support.

    * **Examples**

>            <option name="searchpath">/path/to/aether</option>
>            <option name="searchpath">/path/to/aether;/path/to/aether2</option>

* _cache_: Turn on and off caching
    * **Values**:  on / off

* _cacheClass_: Name of the cache class to use.
* _cacheOptions_: For use with built-in memcache-class.

    * **Values**: `$host`:`$port`

    * **Example**

>            <option name="cacheOptions">localhost:11211</option>
