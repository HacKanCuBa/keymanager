# GPG KeyManager
Simple PHP class to create a simple secure GPG key manager to allow downloading or displaying of GPG keys. The output is always plain text (o a stream for download).

Feel free to use, fork, share, etc. Create pull requests or issues if needed :)

You can see it working at [gpg.hackan.net](https://gpg.hackan.net).

*PHP version*: This works for **PHP v5.6+**. If you are on an older version, replace the constant-defined array in keymanager.class.php:15 for a variable, then call it in keymanager.class.php:readConfig:64-65.

## Deploy

Needs a PHP v5.6+ server. Everything required is provided by this repo, so just git clone or download and extract files. 

Note: for non-apache web servers, make sure to replicate functionality of `.htaccess` files, which also provided here. Basically, deny access to subdirectories and redirect every query to index.

Then add your key files ascii-armored in the `keys` directory: `gpg -a --export 0x{key id} > keys/0x{key id}.asc`. In my case, I would: `gpg -a --export 0x35710D312FDE468B > keys/0x35710D312FDE468B.asc`. Modify the config file `config/config.json` by listing key id vs key file name:  

```json
{
    "options": {
        "show_help": true,
        "show_keys": false,
        "default_first_key": true
    },
    "keys": {
        "35710D312FDE468B": "keys/0x35710D312FDE468B.asc"
    }
}
```

As you could probably guess, you can use any key file name you want, and actually even any key id string you want (note that `KeyManager::setKeyid()` only accepts hexadecimal characters as key id). You can also add as many keys as you want.

## Configuration

Edit `config/config.json` (it's on a separated dir for security). You can change its name and location, and call it when creating a new `KeyManager` instance: `$keymager = new KeyManager('config/config.json');`.

### Options

* show_help (true/false): help message when wrong parameter or value is input.
* show_keys (true/false): show valid key values when showing help.
* default_first_key (true/false): if no key selected by the user, then show the first key.

### Keys

List of key ids and the relative path to the file from where the class file is located, or an absolute path.

Example of relative path:

```json
    "keys": {
        "35710D312FDE468B": "keys/0x35710D312FDE468B.asc"
    }
```

Example of absolute path:

```json
    "keys": {
        "35710D312FDE468B": "/srv/http/keymanager/keys/0x35710D312FDE468B.asc"
    }
```

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
    }
}
```

### Internal default

Config file is mandatory, and an error is shown if missing. However, the whole *options* part is not, and can be missing. Or any key of it could be missing w/o issue. The internal default is always **false** for every option.

## License

KeyManager by [HacKan](https://twitter.com/hackancuba) GNU GPL v3.0 or newer.

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

