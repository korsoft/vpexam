import merge from 'lodash.merge';
import isPlainObject from 'lodash.isplainobject';

/**
 * Merge two options objects, recursively merging **only** plain object
 * properties.  Previously `deepMerge`.
 *
 * @param  {Object} object    The destination object
 * @param  {...Object} source One or more objects to merge into the first
 *
 * @returns {Object}          The updated first object
 */
export default function mergeOptions(object={}) {
    // Allow for infinite additional object args to merge
    Array.prototype.slice.call(arguments, 1).forEach(function(source) {
        // Recursively merge only plain objects
        // All other values will be directly copied
        merge(object, source, function(a, b) {
            // If we're not working with a plain object, copy the value as is
            if (!isPlainObject(a)) {
                return mergeOptions({}, b);
            }
        });
    });

    return object;
}