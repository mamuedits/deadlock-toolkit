function showMessage(message) {
    const modal = document.createElement('div');
    modal.className = 'modal';
    modal.id = 'messageModal';
    
    const modalContent = document.createElement('div');
    modalContent.className = 'modal-content';
    
    const closeSpan = document.createElement('span');
    closeSpan.className = 'close';
    closeSpan.innerHTML = '&times;';
    closeSpan.onclick = function() {
        modal.style.display = 'none';
    };
    
    const messagePara = document.createElement('p');
    if (message.includes('Warning')) {
        messagePara.className = 'error-message';
    } else {
        messagePara.className = 'success-message';
    }
    messagePara.textContent = message;
    
    modalContent.appendChild(closeSpan);
    modalContent.appendChild(messagePara);
    modal.appendChild(modalContent);
    
    document.body.appendChild(modal);
    
    modal.style.display = 'block';
    
    window.onclick = function(event) {
        if (event.target == modal) {
            modal.style.display = 'none';
        }
    }
}

document.addEventListener('DOMContentLoaded', function() {
    const forms = document.querySelectorAll('form');
    
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            let valid = true;
            
            const inputs = form.querySelectorAll('input[required]');
            inputs.forEach(input => {
                if (!input.value) {
                    valid = false;
                    input.style.borderColor = 'red';
                } else {
                    input.style.borderColor = '#ddd';
                }
            });
            
            if (!valid) {
                e.preventDefault();
                showMessage('Please fill in all required fields!');
            }
        });
    });
});
