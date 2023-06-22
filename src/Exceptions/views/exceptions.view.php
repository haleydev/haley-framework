<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Roboto+Mono&display=swap" rel="stylesheet">

    <script src="{{ dirname(__DIR__) . '/Debug/resources/' }}jquery.min.js"></script>
    <title>DEBUG</title>
</head>

<body>
    <div class="header-menu">
        <div class="error_message">
            <p>
                <?php echo $error_message ?>
            </p>
            <p>
                <?php echo $error_file ?>
            </p>
        </div>

        <div class="menu">
            <p class="menu-btn menu-btn-active" data-btn="code">code</p>
            <p class="menu-btn" data-btn="error">error</p>
            <p class="menu-btn" data-btn="request">request</p>
            <p class="menu-btn" data-btn="header">header</p>
        </div>
    </div>

    <div class="sections">
        <section class="debug">
            <div class="box">
                <div class="box-debug" id="code">
                    <section class="code">
                        <?php echo $code ?>
                    </section>
                </div>

                <div class="box-debug display-none" id="error">
                    @if($error_all)
                    <p class="none-border">
                    <pre>{{ var_dump($value) }}</pre>
                    </p>
                    @endif
                </div>

                <div class="box-debug display-none" id="request">
                    <p>METHOD =>
                        <?php echo $method ?>
                    </p>
                    <?php if ($request_all != false) : ?>
                    <?php foreach ($request_all as $key => $value) : ?>
                    <p>
                        <?php echo $key ?> =>
                        <?php htmlspecialchars(print_r($value)) ?>
                    </p>
                    <?php endforeach ?>
                    <?php else : ?>
                    <p>[]</p>
                    <?php endif ?>
                </div>

                <div class="box-debug display-none" id="header">
                    <?php if ($headers) : ?>
                    <?php foreach ($headers as $key => $value) : ?>
                    <p>
                        <?php echo $key ?> =>
                        <?php echo $value ?>
                    </p>
                    <?php endforeach ?>
                    <?php endif ?>
                </div>
            </div>
        </section>
    </div>

    <style>
        @media (max-width:700px) {
            .error_message p {
                font-size: 12px !important;
            }
        }

        @media (max-width:1100px) {
            .error_message p {
                text-align: unset !important;
            }
        }

        body {
            height: 100vh;
            background: #fbfbfb;
            display: flex;
            /* width: 50%; */
            flex-direction: column;
            /* justify-content: space-between; */
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            outline: none;
            -webkit-tap-highlight-color: rgba(255, 255, 255, 0);
            scroll-behavior: smooth;
            font-family: 'Roboto Mono', monospace;
            color: rgb(112 112 112);
            word-break: break-all;
        }

        pre {
            border: none;
            font-size: 14px;
        }

        ::-webkit-scrollbar {
            width: 6px;
            background: none;
        }

        ::-webkit-scrollbar-thumb {
            background-color: #9d9d9d;
            border-radius: 0px;
        }

        .sections {
            max-height: 100%;
            overflow: overlay;
            display: flex;
        }

        .display-none {
            display: none !important;
        }

        .none-border {
            border: unset !important;
        }

        .code {
            background: #fbfbfb;
            overflow-y: auto;
            overflow-x: hidden !important;
            padding: 6px 0px;
            /* border-left: 1px solid #d1d1d1; */
            /* box-shadow: 0px 2px 3px 0px rgb(24 24 24 / 65%); */
            height: 100%;
            width: 100%;
        }

        .code p {
            padding: 2px;
            display: flex;
            align-items: center;
            font-size: 12px !important;
            color: #939393;
            border-bottom: none !important;
        }

        /* start menu */
        .menu {
            display: flex;
            padding: 8px 10px;
            border-bottom: 1px solid #d1d1d1;
            background: #dbdbdb;
            border-top: 1px solid #d1d1d1;
        }

        .menu-btn-active {
            color: #b37070 !important;
            /* font-weight: bolder; */
        }

        .menu-btn {
            cursor: pointer !important;
            font-size: 14px;
            cursor: pointer;
            color: #6c6c6c;
            padding: 0px 8px;
        }

        .menu-btn:hover {
            color: #9b4747 !important;
        }

        /* end menu */

        /* debug */
        .debug {
            justify-content: center;
            display: flex;
            background: #fbfbfb;
            width: 100%;
            position: relative;
        }

        .header-menu {
            width: 100%;
            z-index: 900;

        }

        .box {
            max-height: 100%;
            width: 100%;
            overflow-x: auto;
        }

        .box-debug {
            padding: 5px;
            overflow: auto;
            overflow-x: auto;
        }

        .box-debug p {
            font-size: 13px;
            color: #747474;
            padding: 3px 8px;
            border-bottom: 1px solid #d1d1d1;
        }

        .box-debug p:last-child {
            border-bottom: unset !important;
        }

        .error_message {
            background: #303030;
            padding: 8px;
            /* box-shadow: 0px 2px 3px 0px rgb(24 24 24 / 65%); */
        }

        .error_message p {
            font-size: 14px;
            /* font-weight: bolder; */
            text-align: center;
            color: #cbcbcb;
        }

        .line-number {
            color: #7e7e7e;
            font-size: 12px;
            min-width: 30px;
            margin-left: 8px;
            -webkit-touch-callout: none;
            -webkit-user-select: none;
            -khtml-user-select: none;
            -moz-user-select: none;
            -ms-user-select: none;
            user-select: none;
        }

        .error-line {
            background: #e1e1e1;
            color: #bd5b5b !important;
        }
    </style>


    <script>
        document.getElementById("error_line").scrollIntoView();
        $(".menu-btn").click(function() {
            tab = $(this).data('btn');
            $('.menu-btn').removeClass('menu-btn-active');
            $(this).addClass('menu-btn-active');
            $('.box-debug').addClass('display-none');
            $('#' + tab).removeClass('display-none');
        });
    </script>
</body>

</html>