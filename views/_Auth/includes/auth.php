<?php
$session = $this->session;
?>
<div class="row">
    <div class="col-xs-12">
        <br />
        <form id="auth_form" method="POST" action="/auth/enter/">
            <fieldset>
                <legend><span class="glyphicon glyphicon-lock"></span> Вход в базу 3D моделей ХЮФ</legend>
                <div class="form-group">
                    <label for="inputLogin">Логин: </label><span style="color: red;"><b> <?= $session->hasFlash('wrongLog') ? $session->getFlash('wrongLog') : "" ?></b></span>
                    <input type="text" name="login" id="Login" required placeholder="Введите логин" class="form-control" value="<?=$login; ?>" />
                </div>
                <div class="form-group">
                    <label for="inputPassword">Пароль: </label><span style="color: red;"><b> <?= $session->hasFlash('wrongPass') ? $session->getFlash('wrongPass') : "" ?></b></span>
                    <input type="password" name="pass" id="Pass" required placeholder="Введите пароль" class="form-control">
                </div>
                <div class="form-group">
                    <input type="checkbox" name="memeMe" id="memeMe"/>
                    <label for="memeMe" class="memeMe">&#160;Запомнить меня</label>
                    <br>
                    <button type="submit" name="submit" class="btn btn-default"><span class="glyphicon glyphicon-log-in"></span>&#160; Войти</button>
                </div>
            </fieldset>
        </form>
    </div>
</div>

<?php
//debug($users,'users');
//debug(password_hash('vadim_B',PASSWORD_DEFAULT),'pass');
?>