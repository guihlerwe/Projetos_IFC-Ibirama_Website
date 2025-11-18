// Copyright (c) [year] [fullname]
// 
// This source code is licensed under the MIT license found in the
// LICENSE file in the root directory of this source tree.

document.addEventListener('DOMContentLoaded', () => {
    const senhaInput = document.getElementById('senha');
    const toggle = document.querySelector('.toggle-senha');

    if (!senhaInput || !toggle) {
        return;
    }

    toggle.addEventListener('click', () => {
        const mostrando = senhaInput.type === 'text';
        senhaInput.type = mostrando ? 'password' : 'text';
    });
});
