class was {
	constructor(current, file) { //pass this to WAS to get the elements id
		this.file=file //url to query php when found
		this.php="was.php" //php server to upload to
		this.node=current.parentNode //gets parent div

		this.img=current //instance is called from inside img
		this.fspan=document.createElement("SPAN") //file name span
		this.fspan.innerText=file
		this.node.append(this.fspan) //appends it to the parent div

		this.link=document.createElement("a") //hidden link for downloading
		this.link.style.display="none"
		this.node.append(this.link)

		this.active=false //prevents double clicking

		this.node.onclick=()=>{ if (!this.active) this.run() }

		//caches imgs
		this.minerimg=new Image()
		this.doneimg=new Image()
		this.errorimg=new Image()
		this.minerimg.src="mining.gif"
		this.doneimg.src="done.png"
		this.errorimg.src="error.png"
	}
	async run() {
		this.active=true //makes sure miner isnt being ran more then once

		this.json=await this.challenge() //waits for response from server
		this.key=this.json["challenge"]
		this.bits=this.json["bits"]
		if (this.bits>32||this.bits<0) return //32 or more, or 0 and below are unreasonable, dont mine
		
		this.img.onload=()=>this.color("#ff006e")
		this.img.src=this.minerimg.src //the img will already be in cache

		this.index=1 //starts at 1 since 0 would cause (index%2500==0) to be true
		
		var mine=()=>{
			for(;;this.index++) { //loops forever until POW is completed
				var hash=sha512(this.key+this.index)
				var digest='' //cast to string
				for (var j=0;j<(~~((this.bits+3)/4));j++) { //loops through each character of hex digest to create binary digest
					var dec=parseInt(hash[j], 16) //turns hex to dec
					var bin=dec.toString(2) //turns hex into binary
					digest+="0".repeat(4-bin.length)+bin //adds leading 0s, eg turns "10" into "0010"
				}
				if (Number(digest.substr(0, this.bits))==0) { //if number of leading zeros is met
					this.pow=this.index
					this.done() //download file
					break
				}
				if (this.index%2500==0) { //miner must start and stop to prevent "slow script" error
					setTimeout(mine, 0)
					this.index++ //makes sure next miner picks up where this one left off
					break //prevents the miner from running after its interval is over
				}
			}
		}
		mine() //runs until POW is done
	}
	async done() { //sends finished POW to server
		this.img.onload=()=>this.color("#6eff00")
		this.img.src=this.doneimg.src
		
		var form=new FormData()
		form.append("challenge", this.key)
		form.append("pow", this.pow)
		form.append("file", this.file)

		var resp=await fetch(this.php, {method:"post", credentials:"same-origin", body:form})
			.then(e=>e.text())
			.then(e=>{
				if (e.includes("ERROR")) { //if there is an error returned
					this.img.onload=()=>{
						this.fspan.innerText=e //show error
						this.color("#ff0000")
					}
					this.img.src=this.errorimg.src //load error img
				}
				else {
					this.link.href=e //sets invisible link
					this.link.click() //clicks the link, downloads in background
				}
			})
	}
	challenge() { //gets new challenge from server
		var form=new FormData()
		form.append("challenge", 1) //second param can be anything, php only checks if challenge is set
		form.append("file", this.file)

		return fetch(this.php, {method:"post", credentials:"same-origin", body:form})
			.then(e=>e.json())
			.then(e=>{return e}) //return the text output
	}
	color(str) { //changes color for different modes etc
		this.fspan.style.borderBottom="4px solid "+str //line
		this.fspan.style.color=str //text
	}
}