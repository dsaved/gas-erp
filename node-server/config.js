// ========
module.exports = {
    // socket configurations
    configs: {
        dev_mode: true,
        log: function(data) {
            if (this.dev_mode) console.log(data)
        }
    },
};