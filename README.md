## AutoParis 
Autoparis is a library, that extends [j4mie's paris ORM](http://github.com/j4mie/paris) for automated scheme creation and keeping it up to date.

## How to use it?
### Installing 
Autoparis can be simply installed by [composer](http://getcomposer.org), actual install information you can found at [page on packagist](https://packagist.org/packages/shadowprince/autoparis).

### Requirements
1. Model should extend __\Autoparis\AutoModel__ and provide public ( *non static* ) method __getFields()__. It should return array of instances of __\Autoparis\Field__ or classes extending it.
2. **public static $_field** of every model should be setted up.
3. You should provide **lookup_models()** function in __bin/autoparis.php__, that'll return array of models classes
4. And you should properly configure idiorm when you start autoparis (you can get trough it simply including your project boostrap, that will call __ORM::configure__'s)

### Usage
Autoparis is a cli-tool, located in __bin/autoparis.php__. You can get help trough __--help__. Autoparis has behavior like django's tool.
By default, autoparis will update all schemes for models returned by **lookup_models()**
Like django, autoparis will not modify you'r tables if you dont provide __--force__ option, __becose that action can damage you'r data__, so dont run it on production db's.

## Documentation
There is [documentation](http://github.com/shadowprince/autoparis/wiki), that covers few topics that might be unclear and usefull. 

## Examples

    // model class
    class User extends \Autoparis\AutoModel {
        public static $_table = 'users';

        public function getFields() {
            return [
                new \Autoparis\Int("id", ["nn" => true]),
                new \Autoparis\Varchar("username", 32),
                new \Autoparis\Varchar("password"", 32),
                new \Autoparis\DateTime("joined", ["default" => "now"])
            ];
        }
    
    // in autoparis.php
    ORM::configure(...);

    function lookup_models() {
        return ["\User"];
    }

    $ ./autoparis.php
        Processing \User...
        Up to date.
    mysql [db]> show columns from users;
        +----------+-------------+------+-----+---------+-------+
        | Field    | Type        | Null | Key | Default | Extra |
        +----------+-------------+------+-----+---------+-------+
        | id       | int(11)     | NO   |     | NULL    |       |
        | username | varchar(32) | YES  |     | NULL    |       |
        | password | varchar(32) | YES  |     | NULL    |       |
        | joined   | datetime    | YES  |     | NULL    |       |
        +----------+-------------+------+-----+---------+-------+

