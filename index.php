<?php

// Only valid if PHP7 or greater
// declare(strict_types = 1);

/*
 * AUTHOR : AVONTURE Christophe.
 *
 * Written date : 24 october 2018
 *
 * PHP Password interface
 *
 * For the maximum security, use the Argon2 hashing algorithm and for this reason, requires a
 * least PHP 7.2
 * 
 * Last mod:
 * 2019-01-08 - Abandonment of jQuery and migration to vue.js
 *                  Except for clipboard.min.js which requires jQuery
 */

define('REPO', 'https://github.com/cavo789/php_password');

// Retrieve posted data
$data = json_decode(file_get_contents('php://input'), true);
$task = trim(filter_var(($data['task'] ?? ''), FILTER_SANITIZE_STRING));

if ('hash' == $task) {
    // Hash the password
    $PWD = base64_decode(trim(filter_var(($data['PWD'] ?? ''), FILTER_SANITIZE_STRING)));

    // Default is PASSWORD_BCRYPT
    // Always start with "$2y$" and has a length of 60 characters
    // As from PHP 7.2, best to use Argon2 hashing algorithm.
    // According to cryptographic expert Argon is one of the best cryptographic function.
    // This algorithm had won  Password Hashing Competition in July 2015.

    // --> the following line requires PHP 7.2 but make sure that ARGON2I is well defined
    if (defined('PASSWORD_ARGON2I')) {
        $PWD = password_hash($PWD, PASSWORD_ARGON2I);
    } else {
        $PWD = password_hash($PWD, PASSWORD_DEFAULT);
    }

    $arr = [];

    if (false !== $PWD) {
        $arr['hash'] = base64_encode($PWD);
    } else {
        // Ouch... an error has occurred
        $arr['hash']   = '';
    }

    header('Content-Type: text/html');
    echo json_encode($arr);
    die();
} elseif ('login' == $task) {
    // Just to make sure that the generated hash is correct
    $PWD = base64_decode(trim(filter_var(($data['PWD'] ?? ''), FILTER_SANITIZE_STRING)));
    $hash = base64_decode(trim(filter_var(($data['hash'] ?? ''), FILTER_SANITIZE_STRING)));

    $check = password_verify($PWD, $hash)
        ? '<span style="color:green;">(success, hash validated)</span>'
        : '<span style="color:red;font-weight:bolder;">(FAILURE, an error has occurred)</span>';

    header('Content-Type: text/html');
    echo base64_encode($check);
    die();
}

// Get the GitHub corner
$github = '';
if (is_file($cat = __DIR__ . DIRECTORY_SEPARATOR . 'octocat.tmpl')) {
    $github = str_replace('%REPO%', REPO, file_get_contents($cat));
}
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8"/>
        <meta name="author" content="Christophe Avonture" />
        <meta name="robots" content="noindex, nofollow" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <meta http-equiv="content-type" content="text/html; charset=UTF-8" />
        <meta http-equiv="X-UA-Compatible" content="IE=9; IE=8;" />
        <title>PHP - Password</title>
        <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css" rel="stylesheet" type="text/css">
        <link href="https://cdnjs.cloudflare.com/ajax/libs/prism/1.13.0/themes/prism.min.css" rel="stylesheet" type="text/css">
        <style>
            details {
                margin: 1rem;
            }
            summary {
                font-weight: bold;
            }
            pre {
                white-space: pre-wrap;       /* css-3 */
                white-space: -moz-pre-wrap;  /* Mozilla, since 1999 */
                white-space: -pre-wrap;      /* Opera 4-6 */
                white-space: -o-pre-wrap;    /* Opera 7 */
                word-wrap: break-word;       /* Internet Explorer 5.5+ */
            }
            #checkPwd {
                font-style: italic;
            }
            .error {
                color: red;
                font-weight: bold;
            }
            textarea {
                margin-top: 10px;
            }
        </style>
    </head>
    <body>
        <?php echo $github; ?>
        <div class="container">
            <div class="page-header"><h1>PHP Password</h1></div>
            <div id="app" class="container">
                <how-to-use demo="">
                    <p>Since PHP 7.x it is recommended to use the native <strong>password_hash()</strong>
                        function 
                        (<a href="https://www.atyantik.com/managing-passwords-correct-way-php/" target="_blank" rel="noopener noreferrer">read more</a>).
                    </p>
                    <p>MD5 should be avoided since there are plenty of md5 dictionaries for helping 
                        to "crack" MD5 passwords like f.i. https://crackstation.net/.</p>
                    <p>Note: since password_hash() is native, therefore there are no dependencies 
                        with an external library.</p>
                    <div class="row">
                        <div class="col-sm">
                            <ul>
                                <li>Type (or paste) the character string in the textarea below and 
                                    click on the Hash button; you'll get the hash of the password.</li>
                                <li>Use the password like in the code sample that will be given.</li>
                            </ul>
                        </div>
                    </div>
                </how-to-use>

                <div class="form-group">
                    <label for="PWD">Enter below the character string you want to use as password:</label>
                    <input type="text" @keydown="doReset" class="form-control" v-model="PWD" name="PWD" width="100"></input>
                </div>

                <button type="button" @click="getHash" class="btn btn-primary">Hash</button>

                <hr/>

                <div v-if="HASH">
                    <p>The hash of <strong class="password">{{ PWD }}</strong> gives 
                    <strong class="hash">{{ HASH }}</strong>&nbsp;<span id="checkPwd" v-html="CHECK"></span></p>

                    <button class="btnClipboard d-none" data-clipboard-target=".hash">
                        Copy to clipboard
                    </button>

                    <hr/>

                    <p>Sample PHP code:</p>
                    <pre><code id="PHP_Sample" class="language-php">
// 1. For instance, retrieve the password from a protected file, outside the public folder
$hash = file_get_contents('../public/site/password.json');

// $hash now contain the resulting of password_hash("your_password", PASSWORD_DEFAULT)
// For instance $hash is equal to '{{ HASH }}'

// 2. Get the filled-in password, for instance, from a submitted form
$password = filter_input(INPUT_POST, 'password', FILTER_SANITIZE_STRING);

// 3. And verify if the filled in password is the expected one
if (password_verify($password, $hash)) {
    echo 'You can enter to this room, the password is correct.';
}
                    </code></pre>
                    <p>Store for instance the hash of this password in a database or any protected file
                    (best outside your public folder) and don't use anymore your password 
                    in plain text but just verify the hash using <strong>password_verify()</strong>.</p>
                    <p><em>Info: the hash will start with '$2y$' when the used algorithm is BCRYPT and with 
                    '$argon2i$' when Argon2i was used (which is much better).</em></p>
                </div>
            </div>
        </div>
        <script src="https://unpkg.com/vue"></script>
        <script src="https://unpkg.com/axios/dist/axios.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.13.0/prism.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.13.0/components/prism-php.js"></script>

        <!-- Clipboard requires jQuery -->
        <script src="https://code.jquery.com/jquery-3.3.1.min.js" integrity="sha256-FgpCb/KJQlLNfOu91ta32o/NMZxltwRo8QtmkMRdAu8=" crossorigin="anonymous"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/clipboard.js/2.0.0/clipboard.min.js"></script>

        <script type="text/javascript">
            Vue.component('how-to-use', {
                props: {
                    demo: {
                        type: String,
                        required: true
                    }
                },
                template:
                    `<details>
                        <summary>How to use?</summary>
                        <div class="row">
                            <div class="col-sm">
                                <slot></slot>
                            </div>
                            <div v-if="demo" class="col-sm"><img v-bind:src="demo" alt="Demo"></div>                            
                        </div>
                    </details>`
            });

            var app = new Vue({
                el: '#app',
                data: {
                    PWD: 'MyPasswordIsSecret',
                    HASH: '',
                    CHECK: ''
                },
                methods: {
                    doReset() {
                        this.HASH = '';
                        this.CHECK = '';
                    },
                    getHash() {
                        var $data = {
                            task: 'hash',
                            PWD: window.btoa(this.PWD)
                        }
                        axios.post('<?php echo basename(__FILE__); ?>', $data)
                        .then(response => {
                            this.HASH = window.atob(response.data.hash)

                            // Check password to make sure it was ok
                            var $data = {
                                task: 'login',
                                PWD: window.btoa(this.PWD),
                                hash: window.btoa(this.HASH)
                            }
                            axios.post('<?php echo basename(__FILE__); ?>', $data)
                            .then(response => {
                                this.CHECK = window.atob(response.data)
                            })
                        })
                        .catch(function (error) {console.log(error);})
                        .then(function() {
                            if (typeof Prism === 'object') {
                                // Use prism.js and highlight source code
                                Prism.highlightAll();
                            }

                            // jQuery part, handle the Copy in the clipboard feature
                            if (typeof ClipboardJS === 'function') {
                               if (this.HASH!=='') {
                                   $('.btnClipboard').removeClass('d-none').removeAttr("disabled");
                                   var clipboard = new ClipboardJS('.btnClipboard');
   
                                   clipboard.on('success', function(e) {
                                       alert('Password hash copied!');
                                       e.clearSelection();
                                   });
                               }
                            };
                        });
                    }
                }
            });
        </script>
    </body>
</html>
