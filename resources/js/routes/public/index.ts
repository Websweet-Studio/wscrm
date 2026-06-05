import hosting from './hosting'
import domains from './domains'
const publicMethod = {
    hosting: Object.assign(hosting, hosting),
domains: Object.assign(domains, domains),
}

export default publicMethod