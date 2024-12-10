export function getCookie(name) {
    const value = `; ${document.cookie}`
    const parts = value.split(`; ${name}=`)
    if (parts.length === 2) return parts.pop().split(';').shift()
}

export function getGoogleCID() {
    return getCookie('_ga').replace('GA1.1.', '')
}
