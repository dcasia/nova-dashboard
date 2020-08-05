import Dot from 'dot-object'

export const dotter = new Dot('__', false, true, false)

/**
 * Convert strings that looks like numbers to numbers, boolean to boolean etc..
 */
export const castValuesToNativeTypes = options => {

    const bag = {}

    for (const key in options) {

        const element = options[ key ]

        if (_.isObject(element)) {

            bag[ key ] = castValuesToNativeTypes(element)

        } else {

            try {

                bag[ key ] = JSON.parse(options[ key ])

            } catch {

                bag[ key ] = options[ key ]

            }

        }

    }

    return bag

}
