// Example implementation for Clients/Index.vue

// Replace the current search section with:
<UnifiedSearchFilter
    title="Search Clients"
    :search="filters.search"
    search-placeholder="Search by name, email, or phone..."
    :filters="filters"
    :result-count="clients.total"
    :primary-filters="[
        {
            key: 'status',
            label: 'Status',
            type: 'select',
            span: 2,
            options: [
                { value: 'active', label: 'Active' },
                { value: 'inactive', label: 'Inactive' },
                { value: 'converted', label: 'Converted' }
            ]
        },
        {
            key: 'source',
            label: 'Source',
            type: 'select',
            span: 2,
            options: [
                { value: 'website', label: 'Website' },
                { value: 'referral', label: 'Referral' },
                { value: 'social_media', label: 'Social Media' },
                { value: 'walk_in', label: 'Walk In' },
                { value: 'phone_call', label: 'Phone Call' }
            ]
        }
    ]"
    :secondary-filters="[
        {
            key: 'budget_min',
            label: 'Min Budget',
            type: 'number',
            placeholder: 'Minimum budget (₱)'
        },
        {
            key: 'budget_max', 
            label: 'Max Budget',
            type: 'number',
            placeholder: 'Maximum budget (₱)'
        },
        {
            key: 'broker_id',
            label: 'Assigned Broker',
            type: 'select',
            options: brokers.map(broker => ({
                value: broker.id,
                label: broker.name
            }))
        },
        {
            key: 'preferred_location',
            label: 'Preferred Location',
            type: 'select',
            options: municipalities.map(city => ({
                value: city,
                label: city
            }))
        }
    ]"
    @search-change="(value) => { filters.search = value; filterClients(); }"
    @filter-change="(key, value) => { filters[key] = value; filterClients(); }"
    @clear-filters="clearAllFilters"
/>

// Add to script setup:
const clearAllFilters = () => {
    Object.keys(filters.value).forEach(key => {
        filters.value[key] = ''
    })
    filterClients()
}