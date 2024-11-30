function go(url) {
    window.location.href = url;
}

function avisoCookies({
    message = 'Utilizamos cookies para que vocÃª tenha a melhor experiÃªncia em nosso site. Para saber mais acesse nossa pÃ¡gina de PolÃ­tica de Privacidade',
}) {
    var check = localStorage.getItem('avisoCookies')
    if (!check) {
        var body = document.getElementsByTagName('body')[0];
        body.innerHTML += `
            <div id="aviso-cookies">
                <span id="texto-cookies">${message}</span>
                <button class="btn btn-primary" id="entendi-cookies">Entendi</button>
            </div>`;
        document.getElementById('entendi-cookies').addEventListener('click', function () {
            localStorage.setItem("avisoCookies", "accept");
            document.getElementById('aviso-cookies').remove()
        })
    }
}

function calcularMinutos(tempo) {
  
    var partes = tempo.split(":");
    
    var horas = parseInt(partes[0]);
    var minutos = parseInt(partes[1]);
    var segundos = parseInt(partes[2]);
    
    var totalMinutos = horas * 60 + minutos + Math.round(segundos / 60);
    
    return totalMinutos;
}

function multiplicarTempo(tempo, multiplicador) {
    var partesTempo = tempo.split(":");
    var horas = parseInt(partesTempo[0]);
    var minutos = parseInt(partesTempo[1]);
    var segundos = parseInt(partesTempo[2]);
  
    var tempoTotalSegundos = horas * 3600 + minutos * 60 + segundos;
  
    var tempoMultiplicadoSegundos = tempoTotalSegundos * multiplicador;
  
    var horasMultiplicadas = Math.floor(tempoMultiplicadoSegundos / 3600);
    var minutosMultiplicados = Math.floor((tempoMultiplicadoSegundos % 3600) / 60);
    var segundosMultiplicados = Math.floor(tempoMultiplicadoSegundos % 60);
  
    var tempoResultado = 
        pad(horasMultiplicadas) + ":" + 
        pad(minutosMultiplicados) + ":" + 
        pad(segundosMultiplicados);
  
    return tempoResultado;
}

function pad(num) {
    return (num < 10 ? "0" : "") + num;
}

function validaVazio(seletor) {
    var valor = seletor.value;

    if (valor === '') {
        seletor.classList.remove('is-valid');
        seletor.classList.add('is-invalid');
        return false;
    }

    seletor.classList.remove('is-invalid');
}

function mensagem(mensagem, type = "alert-danger") {
    var alertDiv = document.createElement("div");
    alertDiv.className = "alert " + type + " alert-dismissible position-absolute z-2 w-100 alert-dismissible mt-1 d-flex justify-content-between align-items-center";
    alertDiv.role = "alert";
    alertDiv.innerHTML = mensagem + '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>';
    document.body.prepend(alertDiv);
}

function showLoader() {
    if (!document.getElementById("loader")) {
        var loaderDiv = document.createElement("div");
        loaderDiv.id = "loader";
        document.documentElement.appendChild(loaderDiv);
    } else {
        document.getElementById("loader").style.display = "block";
    }
}

function removeLoader() {
    document.getElementById("loader").style.display = "none";
}

function getInvalid(mensagem, id) {
    return '<div id="' + id + '" class="invalid-feedback">' + mensagem + '</div>';
}

function getValid(mensagem, id) {
    return '<div id="' + id + '" class="valid-feedback">' + mensagem + '</div>';
}

function loadChoices() {
    let multipleText = document.querySelectorAll("input[type=multiple-text]");
    let select = document.querySelectorAll("select");

    if (!document.querySelectorAll(".choices__inner").length) {
        if (select.length) {
            select.forEach(element => {
                new Choices(element, {
                    noResultsText: 'resultados não encontrados',
                    itemSelectText: 'Precione para selecionar',
                });
            });
        }

        if (multipleText.length) {
            multipleText.forEach(element => {
                new Choices(element, {
                    delimiter: ',',
                    editItems: true,
                    removeItemButton: true,
                    addItemText: (value) => {
                        return `Aperte Enter para adicionar <b>"${value}"</b>`;
                    },
                });
            });
        }
    }
}

function validaTelefone(telefone) {
    var telefone = telefone.value.replace(/\D/g, '');

    if (!(telefone.length >= 10 && telefone.length <= 11)) {
        document.getElementById("telefone").classList.remove('is-valid');
        document.getElementById("telefone").classList.add('is-invalid');
        return false;
    }

    if (telefone.length === 11 && parseInt(telefone.substring(2, 3)) !== 9) {
        document.getElementById("telefone").classList.remove('is-valid');
        document.getElementById("telefone").classList.add('is-invalid');
        return false;
    }

    for (var n = 0; n < 10; n++) {
        if (telefone === new Array(11).join(n) || telefone === new Array(12).join(n)) {
            document.getElementById("telefone").classList.remove('is-valid');
            document.getElementById("telefone").classList.add('is-invalid');
            return false;
        }
    }

    var codigosDDD = [11, 12, 13, 14, 15, 16, 17, 18, 19,
        21, 22, 24, 27, 28, 31, 32, 33, 34,
        35, 37, 38, 41, 42, 43, 44, 45, 46,
        47, 48, 49, 51, 53, 54, 55, 61, 62,
        64, 63, 65, 66, 67, 68, 69, 71, 73,
        74, 75, 77, 79, 81, 82, 83, 84, 85,
        86, 87, 88, 89, 91, 92, 93, 94, 95,
        96, 97, 98, 99];

    if (codigosDDD.indexOf(parseInt(telefone.substring(0, 2))) === -1) {
        document.getElementById("telefone").classList.remove('is-valid');
        document.getElementById("telefone").classList.add('is-invalid');
        return false;
    }

    document.getElementById("telefone").classList.remove('is-invalid');
    return true;
}

function validaEmail() {
    var er = new RegExp(/^[A-Za-z0-9_\-\.]+@[A-Za-z0-9_\-\.]{2,}\.[A-Za-z0-9]{2,}(\.[A-Za-z0-9])?/);
    var email = document.getElementById("email").value;

    if (email === '' || !er.test(email)) {
        document.getElementById("email").classList.remove('is-valid');
        document.getElementById("email").classList.add('is-invalid');
        return false;
    }

    document.getElementById("email").classList.remove('is-invalid');
}

function setEvents() {
    let emails = document.querySelectorAll("input[type=email]")
    if (emails.length) {
        emails.forEach(function (input) {
            input.addEventListener("blur", function () {
                validaEmail(this);
            });
        });
    }

    let requireds = document.querySelectorAll("input[required]")
    if (requireds.length) {
        requireds.forEach(function (input) {
            input.addEventListener("blur", function () {
                validaVazio(this);
            });
        });
    }

    let srequireds = document.querySelectorAll("select[required]")
    if (srequireds.length) {
        srequireds.forEach(function (input) {
            input.addEventListener("blur", function () {
                validaVazio(this);
            });
        });
    }

    let textarea = document.querySelectorAll("textarea[required]")
    if (textarea.length) {
        textarea.forEach(function (input) {
            input.addEventListener("blur", function () {
                validaVazio(this);
            });
        });
    }

    let tels = document.querySelectorAll("input[type=tel]");
    if (tels.length) {
        tels.forEach(function (input) {
            input.addEventListener("blur", function () {
                validaTelefone(this);
            });
        });
    }

    let btnMarcar = document.querySelector("button#btn_massaction_marcar");
    if (btnMarcar) {
        btnMarcar.addEventListener("click", function () {
            let checkboxs = document.querySelectorAll("#massaction");
            if (checkboxs.length) {
                checkboxs.forEach(function (input) {
                    input.checked = true;
                });
            }
        })
    }

    let btnDesmarcar = document.querySelector("button#btn_massaction_desmarcar");
    if (btnDesmarcar) {
        btnDesmarcar.addEventListener("click", function () {
            let checkboxs = document.querySelectorAll("#massaction");
            if (checkboxs.length) {
                checkboxs.forEach(function (input) {
                    input.checked = false;
                });
            }
        })
    }

    let alist = document.querySelectorAll("a.link-main-menu");
    if(alist){
        for (const a of alist) {
            if(a.classList != undefined){
                a.addEventListener("click", function () { 
                    removeActiveMenu()  
                    history.pushState(null, null,a.attributes["url-link"].value);
                    a.classList.add("active");
                })
            } 
        }
    }
}

function removeActiveMenu(){
    let alist = document.querySelectorAll("a.link-main-menu");
    if(alist){
        for (const a of alist) {
            if(a.classList != undefined){
                a.classList.remove("active");
            } 
        }
    }
}

function loadMensagem(){
    fetch("/mensagem")
    .then((response) => {
        return response.text();
    })
    .then((html) => {
        let mensagem = document.querySelector("#mensagem");
        mensagem.innerHTML = mensagem.innerHTML + html;
    });
}

document.addEventListener("DOMContentLoaded", function () {

    var url_atual = window.location.href;
    var url_base = url_atual.split("/");
    url_base = url_base[0] + "//" + url_base[2] + "/";
    var qtd_bara = window.location.href.split("/").length;
    var uri = window.location.pathname;

    loadChoices();
    setEvents();
    loadMensagem();

    document.body.addEventListener('htmx:xhr:loadstart', function (evt) {
        showLoader();
    });

    document.body.addEventListener('htmx:afterSettle', function (evt) {
        loadChoices();
        setEvents();
        removeLoader();
        loadMensagem();
    });

    const sidebarToggle = document.body.querySelector('#sidebarToggle');
    if (sidebarToggle) {
        if (localStorage.getItem('sb|sidebar-toggle') === 'true') {
            document.body.classList.toggle('sb-sidenav-toggled');
        }
        sidebarToggle.addEventListener('click', event => {
            event.preventDefault();
            document.body.classList.toggle('sb-sidenav-toggled');
            localStorage.setItem('sb|sidebar-toggle', document.body.classList.contains('sb-sidenav-toggled'));
        });
    }

    let toggler = document.getElementsByClassName("caret");
    let i;

    for (i = 0; i < toggler.length; i++) {
    toggler[i].addEventListener("click", function() {
        this.parentElement.querySelector(".nested").classList.toggle("active");
        this.classList.toggle("caret-down");
    });
    }

    htmx.config.globalViewTransitions = true;
    htmx.config.defaultFocusScroll = true;

    if (localStorage.getItem("avisoCookies") != "accept") {
        avisoCookies({
            message: 'Utilizamos cookies para que você tenha a melhor experiência em nosso site. Para saber mais acesse nossa página de <a href="\\privacidade">Política de Privacidade</a>'
        });
    }
});