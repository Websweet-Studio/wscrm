import providers from './providers'
import models from './models'
import packages from './packages'
import credits from './credits'
import transactions from './transactions'
const ai = {
    providers: Object.assign(providers, providers),
models: Object.assign(models, models),
packages: Object.assign(packages, packages),
credits: Object.assign(credits, credits),
transactions: Object.assign(transactions, transactions),
}

export default ai