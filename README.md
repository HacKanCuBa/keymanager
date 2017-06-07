# GPG KeyManager
Simple PHP class to create a simple secure GPG key manager to allow downloading or displaying of GPG keys or history. The output is always plain text (o a stream for download).

Feel free to use, fork, share, etc. Create pull requests or issues if needed :)

You can see it working at [gpg.hackan.net](https://gpg.hackan.net).

*PHP version*: This works for **PHP v5.6+**. If you are on an older version, replace the constant-defined array in KeyManager.php:CONFIGSTRUCT for a variable, then call it in KeyManager.php:readConfig.

## Deploy

Needs a PHP v5.6+ server. Everything required is provided by this repo, so just git clone or download and extract files. 

Note: for non-apache web servers, make sure to replicate functionality of `.htaccess` files, which are also provided here. Basically, deny access to subdirectories and redirect every query to index.

Then add your key files ascii-armored in the `keys` directory: `gpg -a --export 0x{key id} > keys/0x{key id}.asc`. In my case, I would: `gpg -a --export 0x35710D312FDE468B > keys/0x35710D312FDE468B.asc`. Modify the config file `config/config.json` by listing key id vs key file name with full path:  

```json
{
    "options": {
        "show_help": true,
        "show_keys": false,
        "default_first_key": true
    },
    "keys": {
        "35710D312FDE468B": "/var/www/data/keys/0x35710D312FDE468B.asc"
    },
    "history": "history.asc"
}
```

As you could probably guess, you can use any key file name you want, and actually even any key id string you want (note that `KeyManager::setKeyid()` only accepts hexadecimal characters as key id). You can also add as many keys as you want.

The *keys* directory can be anywhere in your filesystem, and can be named the way you want (since you are pointing to the keys full path in the config), as long as the user running the web server process has access to it. The same goes to the actual config directory.

## Configuration

Edit `config/config.json` (it's on a separated dir for security). You can change its name and location, and call it when creating a new `KeyManager` instance: `$keymager = new \HC\OpenPGP\KeyManager('config/config.json');`. It's a good practice if you use a random name for the config directory such as `config-5019271e`.

### Options

* show_help (true/false): help message when wrong parameter or value is input.
* show_keys (true/false): show valid key values when showing help.
* default_first_key (true/false): if no key selected by the user, then show the first key.

### Keys

List of key ids and the relative path to the file from where the class file is located, or an absolute path (I recommend using an absolute path).

Example of relative path:

```json
    "keys": {
        "35710D312FDE468B": "../../keys/0x35710D312FDE468B.asc"
    }
```

Example of absolute path:

```json
    "keys": {
        "35710D312FDE468B": "/srv/http/keymanager/keys/0x35710D312FDE468B.asc"
    }
```
### History

**New in v2.x**

Points to a file in the config directory which contains the keys history (see [this example](https://ivan.barreraoro.com.ar/bio/gpg/) if you don't know what I mean). No format restriction: the whole file, as-is, is pushed to the user when requested.

### Default

Provided by this repo:

```json
{
    "options": {
        "show_help": true,
        "show_keys": false,
        "default_first_key": true
    },
    "keys": {
    },
    "history": ""
}
```

### Internal default

The config file is mandatory, and an error is shown if missing. However, the whole *options* part is not, and can be missing. Or any key of it could be missing w/o issue. The internal default is always **false** for every option.

## License

KeyManager by [HacKan](https://keybase.io/hackan) GNU GPL v3.0 or newer.

    Copyright (C) 2017 HacKan (https://hackan.net)

    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program.  If not, see <http://www.gnu.org/licenses/>.

