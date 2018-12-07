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
 */

define('REPO', 'https://github.com/cavo789/php_password');

$task = filter_input(INPUT_POST, 'task', FILTER_SANITIZE_STRING);

if ('hash' == $task) {
    // Hash the password
    $PWD = base64_decode(filter_input(INPUT_POST, 'pwd', FILTER_SANITIZE_STRING));

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

        // Sample php
        $arr['sample'] = base64_encode(
            '// 1. Retrieve the password from a protected file, outside the public folder' . PHP_EOL .
                '$hash = file_get_contents(\'../protected_folder/password.json\');' . PHP_EOL . PHP_EOL .
                '// $hash now contain the resulting of password_hash("your_password", PASSWORD_DEFAULT)' . PHP_EOL .
                '// For instance $hash is equal to \'' . $PWD . '\'' . PHP_EOL .
                '' . PHP_EOL .
                '// 2. Get the filled-in password, for instance, from a submitted form' . PHP_EOL .
                '$password = filter_input(INPUT_POST, \'password\', FILTER_SANITIZE_STRING);' . PHP_EOL .
                '' . PHP_EOL .
                '// 3. And verify if the filled in password is the expected one' . PHP_EOL .
                'if (password_verify($password, $hash)) {' . PHP_EOL .
                '    echo \'You can enter to this room, the password is correct.\';' . PHP_EOL .
                '}'
        );
    } else {
        // Ouch... an error has occurred
        $arr['hash']   = '';
        $arr['sample'] = '';
    }

    header('Content-Type: text/html');
    echo json_encode($arr);
    die();
} elseif ('login' == $task) {
    // Just to make sure that the generated hash is correct
    $PWD  = base64_decode(filter_input(INPUT_POST, 'pwd', FILTER_SANITIZE_STRING));
    $hash = base64_decode(filter_input(INPUT_POST, 'hash', FILTER_SANITIZE_STRING));

    $check = password_verify($PWD, $hash)
        ? '<span style="color:green;">(success, hash validated)</span>'
        : '<span style="color:red;font-weight:bolder;">(FAILURE, an error has occurred)</span>';

    header('Content-Type: text/html');
    echo base64_encode($check);
    die();
}

// Sample password
$PWD = 'MyPasswordIsSecret';

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
            <div class="container">
                <details>
                    <summary>How to use?</summary>
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
                </details>

                <div class="form-group">
                    <label for="PWD">Enter below the character string you want to use as password:</label>
                    <textarea class="form-control" rows="1" id="PWD" name="PWD"><?php echo $PWD; ?></textarea>
                </div>

                <button type="button" id="btnHash" class="btn btn-primary">Hash</button>

                <hr/>

                <div class="d-none" id="Result">
                    <p>The hash of <strong class="password"></strong> gives 
                    <strong class="hash"></strong>&nbsp;<span id="checkPwd"></span></p>

                    <button class="btnClipboard d-none" data-clipboard-target="#Result .hash">
                        Copy to clipboard
                    </button>

                    <hr/>

                    <p>Sample PHP code:</p>
                    <pre><code id="PHP_Sample" class="language-php"></code></pre>
                    <p>Store for instance the hash of this password in a database or any protected file
                    (best outside your public folder) and don't use anymore your password 
                    in plain text but just verify the hash using <strong>password_verify()</strong>.</p>
                    <p><em>Info: the hash will start with '$2y$' when the used algorithm is BCRYPT and with 
                    '$argon2i$' when Argon2i was used (which is much better).</em></p>
                </div>

            </div>
        </div>

        <script type="text/javascript" src="https://code.jquery.com/jquery-3.3.1.min.js" integrity="sha256-FgpCb/KJQlLNfOu91ta32o/NMZxltwRo8QtmkMRdAu8=" crossorigin="anonymous"></script>
        <script type="text/javascript" src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/js/bootstrap.min.js"></script>
        <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.13.0/prism.min.js"></script>
        <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.13.0/components/prism-php.js"></script>
        <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/clipboard.js/2.0.0/clipboard.min.js"></script>
        <script type="text/javascript">

            $(document).ready(function() {
                if (typeof ClipboardJS === 'function') {

                    $('.btnClipboard').removeClass('d-none');

                    var clipboard = new ClipboardJS('.btnClipboard');

                    clipboard.on('success', function(e) {
                        alert('Hash copied!');
                        e.clearSelection();
                    });
                }
            });

            $('#btnHash').click(function(e) {

                e.stopImmediatePropagation();

                var $data = new Object;
                $data.task = "hash";
                $data.pwd = window.btoa($('#PWD').val());

                $.ajax({
                    beforeSend: function() {
                        $('#btnHash').prop("disabled", true);
                    },
                    async: true,
                    type: "POST",
                    url: "<?php echo basename(__FILE__); ?>",
                    data: $data,
                    datatype: "html",
                    success: function (hashPWD) {
                        // hashPWD is a JSON array with two entries, the hash and a sample code
                        $json = jQuery.parseJSON(hashPWD);

                        // if empty, the password_hash() PHP function has failed
                        if ($json.hash !== '') {

                            // Retrieve the original password, in plain text
                            $('#Result .password').html($('#PWD').val());

                            // And display the password_hash() result for that password
                            $('#Result .hash').html(window.atob($json.hash));

                            // Display a PHP sample
                            $('#PHP_Sample').html(window.atob($json.sample));

                            if (typeof Prism === 'object') {
                                // Use prism.js and highlight source code
                                Prism.highlightAll();
                            }

                            // Just to be sure, simulate a login i.e. validate the hash
                            $data.task = "login";
                            $data.pwd = window.btoa($('#PWD').val());
                            $data.hash = $json.hash;
                            $.ajax({
                                async: true,
                                type: "POST",
                                url: "<?php echo basename(__FILE__); ?>",
                                data: $data,
                                datatype: "html",
                                success: function (data) {
                                    $('#btnHash').prop("disabled", false);
                                    $('#checkPwd').html(window.atob(data));
                                }
                            });
                        } else {
                            $('#Result').html('ERROR, something goes wrong when hashing the password...');
                            $('#Result').addClass('error');
                            
                        }

                        // Finally show the result div
                        $('#Result').removeClass('d-none');

                    }
                });
            });
        </script>
    </body>
</html>
