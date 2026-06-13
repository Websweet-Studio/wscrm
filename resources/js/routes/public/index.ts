import hosting from './hosting'
import domains from './domains'
import demos from './demos'
const publicMethod = {
    hosting: Object.assign(hosting, hosting),
domains: Object.assign(domains, domains),
demos: Object.assign(demos, demos),
}

export default publicMethod