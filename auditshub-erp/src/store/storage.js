var mStorage = {
	norm_to_ascii (data) {
		return unescape(encodeURIComponent(data))
	},
	norm_to_unicode (data) {
		return decodeURIComponent(escape(data))
	},
	crypt_sym (data, k) {
		return String.fromCharCode.apply(undefined, data.split('').map(function (c) {
			return c.charCodeAt(0) ^ (k || 13)
		}))
	},
	set (name, data) {
		const cname = name
		const storageValue = mStorage.encrypt(data)
		if (typeof (Storage) !== 'undefined') {
			// Code for localStorage/sessionStorage.
			localStorage.setItem(cname, storageValue)
		} else {
			// Sorry! No Web Storage support..
			const d = new Date()
			d.setTime(d.getTime() + (500 * 24 * 60 * 60 * 1000))
			const expires = `expires=${  d.toUTCString()}`
			document.cookie = `${cname  }=${  storageValue  };${  expires  };path=/`
		}
	},
	get (iname) {
		const cname = iname
		if (typeof (Storage) !== 'undefined') {
			return (localStorage.getItem(cname)) ? mStorage.decrypt(localStorage.getItem(cname)) : localStorage.getItem(cname)
		} else {
			// Sorry! No Web Storage support..
			const name = `${cname  }=`
			const decodedCookie = decodeURIComponent(document.cookie)
			const ca = decodedCookie.split(';')
			for (let i = 0; i < ca.length; i++) {
				let c = ca[i]
				while (c.charAt(0) == ' ') {
					c = c.substring(1)
				}
				if (c.indexOf(name) == 0) {
					return mStorage.decrypt(c.substring(name.length, c.length))
				}
			}
			return null
		}
	},
	remove (name) {
		const cname = name
		if (typeof (Storage) !== 'undefined') {
			// Code for localStorage/sessionStorage.
			localStorage.removeItem(cname)
		} else {
			// Sorry! No Web Storage support..
			const data = ''
			const d = new Date()
			d.setTime(d.getTime() - (100 * 24 * 60 * 60 * 1000))
			const expires = `expires=${  d.toUTCString()}`
			document.cookie = `${cname  }=${  data  };${  expires  };path=/`
		}
	},
	encrypt (data) {
		if (typeof (data) === 'boolean' || typeof (data) === 'number' || Number(data)) {
			return data
		}
		if (data === 'true' || data === 'false') {
			return data
		}

		try {
			data = btoa(this.crypt_sym(this.norm_to_ascii(data)))
		} catch (error) {
			console.log(error)
		} finally {
			return data
		}
	},
	decrypt (data) {
		if (typeof (data) === 'boolean' || typeof (data) === 'number' || Number(data)) {
			return data
		}

		if (data === 'true' || data === 'false') {
			return data
		}

		try {
			data = this.crypt_sym(this.norm_to_unicode(atob(data)))
		} catch (error) {
			console.log(error)
		} finally {
			return data
		}
	}
}

export default mStorage