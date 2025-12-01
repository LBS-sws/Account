function PasswordStrength(passwordID, strengthID) {
    const inp = document.getElementById('input_password')
        const spans = [...document.querySelectorAll('em')]
        inp.oninput = function () {
            const value = this.value
            const r1 = /\d/
            const r2 = /[a-zA-Z]/
            const r3 = /[^a-z0-9]/
            let level = 0 //默认为0级
            if (value.length < 1) {
                level = 0
            }
            if (value.length >= 1 && value.length < 8) {
                level++
            }
            if(value.length >= 8) {
                if (r1.test(value)) {
                    level++
                }
                if (r2.test(value)) {
                    level++
                }
                if (r3.test(value)) {
                    level++
                }
            }
            for (let i = 0; i < spans.length; i++) {
                spans[i].className = ''
                if (i < level) {
                    spans[i].className = 'active'
                }
            }
        }
};

function MobilePasswordStrength(passwordID, strengthID) {
    const inp = document.getElementById('mobile_input_password')
    const spans = [...document.querySelectorAll('i')]
    inp.oninput = function () {
        const value = this.value
        const r1 = /\d/
        const r2 = /[a-zA-Z]/
        const r3 = /[^a-z0-9]/
        let level = 0 //默认为0级
        if (value.length < 1) {
            level = 0
        }
        if (value.length >= 1 && value.length < 8) {
            level++
        }
        if(value.length >= 8) {
            if (r1.test(value)) {
                level++
            }
            if (r2.test(value)) {
                level++
            }
            if (r3.test(value)) {
                level++
            }
        }
        for (let i = 0; i < spans.length; i++) {
            spans[i].className = ''
            if (i < level) {
                spans[i].className = 'active'
            }
        }
    }
};





