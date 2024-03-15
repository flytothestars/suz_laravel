<!-- custom_captcha.blade.php -->

<div id="custom-captcha">
    <div class="captcha-image">
            <img src="{{ $captchaImage ?? '' }}">
    </div>
    <div class="captcha-input">
        <label for="captcha-input"></label>
        <input type="text" placeholder="Введите капчу:" name="captcha_input" id="captcha-input" maxlength="6">
    </div>
    <div class="captcha-actions">
        <button type="button" class="btn btn-success my-4" id="verify-captcha" disabled>
            Подтвердить
            <i class="fas fa-check-circle"></i>
        </button>
        <div class="error-message" id="captcha-error" style="color: red; display: none;">Вы ввели не правильно, попробуйте еще раз.</div>
    </div>
</div>

@section('page-scripts')
<script>
    var loginButton = document.getElementById('login');
    var captchaInput = document.getElementById('captcha-input');
    var verifyButton = document.getElementById('verify-captcha');
    var captchaForm = document.getElementById('custom-captcha');

    captchaInput.addEventListener('input', function() {
        verifyButton.disabled = captchaInput.value.length !== 6;
    });

    // Custom captcha verification logic
    $("#verify-captcha").on("click", function() {
        var captchaInput = $("#captcha-input").val();

        $.ajax({
            url: "/validate-captcha",
            type: "POST",
            dataType: "json",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content")
            },
            data: JSON.stringify({ captchaInput: captchaInput ? captchaInput : "" }),
            success: function(data) {
                if (data.valid) {
                    enableLoginButton();
                } else {
                    $("#captcha-error").show();
                    disableLoginButton();
                }
            },
            error: function(xhr, status, error) {
                console.log(error);
            }
        });
    });



    function enableLoginButton() {
        loginButton.disabled = false;
        captchaForm.hidden = true;
    }

    function disableLoginButton() {
        loginButton.disabled = true;
    }

</script>

<link href="{{ asset('css/captcha.css') }}" rel="stylesheet">
@endsection

