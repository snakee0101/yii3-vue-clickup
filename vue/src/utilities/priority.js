let Priorities = {
    values: [
        {label: 'Clear', value: null, color: '#000'},
        {label: 'Low', value: 1, color: '#cccccc'},
        {label: 'Normal', value: 2, color: '#3e63dd'},
        {label: 'High', value: 3, color: '#ffc53d'},
        {label: 'Urgent', value: 4, color: '#c62a2f'}
    ],
    findByLabel: function(label) {
        return Priorities.values.find(p => p.label == label);
    },
    findByValue: function(value) {
        return Priorities.values.find(p => p.value == value);
    }
};

export default Priorities;