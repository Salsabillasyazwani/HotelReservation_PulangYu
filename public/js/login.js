function hpySwitchTab(tab){
    const slider = document.getElementById('hpySlider');
    const loginBtn = document.getElementById('tabLoginBtn');
    const registerBtn = document.getElementById('tabRegisterBtn');
    const panelLogin = document.getElementById('panelLogin');
    const panelRegister = document.getElementById('panelRegister');
    const subtitle = document.getElementById('hpySubtitle');

    if(tab === 'login'){
      slider.classList.remove('to-register');
      loginBtn.classList.add('active');
      registerBtn.classList.remove('active');
      panelRegister.classList.remove('active');
      panelLogin.classList.add('active');
      subtitle.textContent = 'Selamat datang kembali, silakan masuk ke akun Anda';
    } else {
      slider.classList.add('to-register');
      registerBtn.classList.add('active');
      loginBtn.classList.remove('active');
      panelLogin.classList.remove('active');
      panelRegister.classList.add('active');
      subtitle.textContent = 'Buat akun baru dan nikmati kemudahan reservasi';
    }
  }

  function hpyTogglePassword(inputId, btn){
    const input = document.getElementById(inputId);
    const icon = btn.querySelector('i');
    if(input.type === 'password'){
      input.type = 'text';
      icon.classList.remove('bi-eye-fill');
      icon.classList.add('bi-eye-slash-fill');
    } else {
      input.type = 'password';
      icon.classList.remove('bi-eye-slash-fill');
      icon.classList.add('bi-eye-fill');
    }
  }
