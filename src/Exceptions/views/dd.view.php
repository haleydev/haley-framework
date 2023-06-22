<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
</head>

<style>
  .dd-code,.dd-title{display:flex;max-width:1400px}.dd-code-var,.dd-title{color:#d2721e}*{margin:0;padding:0;box-sizing:border-box;outline:0;-webkit-tap-highlight-color:rgba(255,255,255,0);scroll-behavior:smooth;color:#fff;font-family:monospace;line-height:1.2;font-size:14px}body{background-color:#141414;padding:10px}::-webkit-scrollbar{width:4px;height:8px;background:0 0}::-webkit-scrollbar-thumb{background:#646464;border-radius:0}p{width:fit-content}.dd-title{margin:0 auto;padding:0 10px 10px 0}.dd-code{flex-direction:column;background:#68686817;margin:0 auto 8px;padding:10px;border-radius:6px}.dd-code-array-key{color:#5791d8;font-weight:lighter!important}.dd-code-string-value{color:#13b313;font-weight:600;font-size:13px}.dd-code-arrow{color:#cdcdcd!important}.dd-code-type{color:#5f5f5f!important;cursor:pointer}.dd-code-tags,.dd-code-type:hover{color:#d2721e!important}.display-none{display:none!important}
</style>

{{ $dd }}

<script>
    var elements = document.querySelectorAll('.dd-code-type');

    if(elements) {
        elements.forEach(function (e) {
            e.addEventListener('click',function(c) {
                var token = c.target.dataset.token || null;     
                var ocult = c.target.dataset.ocult || 0;         
                if(!token) return;               

                var lines = document.querySelectorAll(`.${token}`);

                if(!lines) return;

                lines.forEach(function (l) {
                    if(ocult == 1) {
                        l.classList.remove('display-none');
                        c.target.dataset.ocult = 0;
                    }else{
                        l.classList.add('display-none');
                        c.target.dataset.ocult = 1;
                    }                  
                });  
            });        
        });
    } 
</script>
</body>

</html>