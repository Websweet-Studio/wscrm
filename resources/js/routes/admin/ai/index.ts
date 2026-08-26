import settings from './settings'
import providers from './providers'
import models from './models'
import packages from './packages'
import combos from './combos'
import credits from './credits'
import transactions from './transactions'
const ai = {
    settings: Object.assign(settings, settings),
providers: Object.assign(providers, providers),
models: Object.assign(models, models),
packages: Object.assign(packages, packages),
combos: Object.assign(combos, combos),
credits: Object.assign(credits, credits),
transactions: Object.assign(transactions, transactions),
}

export default ai