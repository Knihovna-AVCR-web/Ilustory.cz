/* global ajaxUrl */

import axios from 'axios'

window.changeRecordStatus = function (id, title, status) {
    const question = status === 'valid' ? 'Přesunout mezi platné' : 'Vyřadit'

    if (!confirm(`${question} ${title}?`)) {
        return
    }

    axios
        .post(`${ajaxUrl}?action=change_writing_status`, {
            id: id,
            status: status,
        })
        .then(() => {
            if (!alert('Upraveno')) {
                window.location.reload()
            }
        })
        .catch((error) => {
            alert('Došlo k chybě: ' + error)
        })
}

window.deleteRecord = function (id, title) {
    if (!confirm(`Odstranit ${title}?`)) {
        return
    }

    axios
        .post(`${ajaxUrl}?action=delete_story`, { id: id })
        .then(() => {
            if (!alert('Odstraněno')) {
                window.location.reload()
            }
        })
        .catch((error) => {
            alert('Došlo k chybě: ' + error)
        })
}

window.verify = function (id, title) {
    if (!confirm(`Ověřit ${title}?`)) {
        return
    }

    axios
        .post(`${ajaxUrl}?action=verify_story`, { id: id })
        .then(() => {
            if (!alert('Ověřeno')) {
                window.location.reload()
            }
        })
        .catch((error) => {
            alert('Došlo k chybě: ' + error)
        })
}
