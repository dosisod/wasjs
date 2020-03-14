class WAS {
	constructor(clickedImg, filepath) {
		this.filepath=filepath
		this.endpoint="was.php"
		this.holder=clickedImg.parentNode

		this.img=clickedImg
		this.text=document.createElement("SPAN")
		this.text.innerText=filepath
		this.holder.append(this.text)

		this.hiddenLink=document.createElement("a")
		this.hiddenLink.style.display="none"
		this.holder.append(this.hiddenLink)

		this.started=false

		this.holder.onclick=()=> {
			if (!this.started) this.start()
		}

		//lazy load images
		this.minerImg=new Image()
		this.doneImg=new Image()
		this.errorImg=new Image()
		this.minerImg.src="mining.gif"
		this.doneImg.src="done.png"
		this.errorImg.src="error.png"
	}

	async start() {
		this.started=true

		const json=await this.getChallenge()
		const bits=json["bits"]
		this.key=json["challenge"]

		if (bits<0 || bits>32) {
			//TODO: display an error
			return
		}

		this.img.onload=()=>this.setColor("#ff006e")
		this.img.src=this.minerImg.src

		this.index=1

		const mine=()=>{
			for(;;this.index++) {
				const hash=sha512(this.key + this.index)
				var digest=""

				//loops through each character of hex digest to create binary digest
				for (let i=0; i < (~~((bits + 3) / 4)) ; i++) {
					const binary=parseInt(hash[i], 16).toString(2)

					digest+=("0000" + binary).slice(4)
				}

				if (Number(digest.substr(0, bits))==0) {
					this.pow=this.index
					this.done()
					break
				}

				if (this.index % 2500==0) {
					setTimeout(mine, 0)
					this.index++
					break
				}
			}
		}
		mine()
	}

	async done() {
		this.img.onload=()=>this.setColor("#6eff00")
		this.img.src=this.doneImg.src

		const form=new FormData()
		form.append("challenge", this.key)
		form.append("pow", this.pow)
		form.append("file", this.filepath)

		await fetch(this.endpoint, {
			method:"post",
			credentials:"same-origin",
			body: form
		})
		.then(e=>e.text())
		.then(e=> {
			if (e.includes("ERROR")) {
				this.img.onload=()=> {
					this.text.innerText=e
					this.setColor("#ff0000")
				}
				this.img.src=this.errorImg.src
			}
			else {
				this.hiddenLink.href=e
				this.hiddenLink.click()
			}
		})
	}

	getChallenge() {
		const form=new FormData()
		form.append("challenge", 1)
		form.append("file", this.filepath)

		return fetch(this.endpoint, {
			method:"post",
			credentials: "same-origin",
			body: form
		})
		.then(e=>e.json())
		.then(e=>{ return e })
	}

	setColor(color) {
		this.text.style.borderBottom="4px solid "+color
		this.text.style.color=color
	}
}