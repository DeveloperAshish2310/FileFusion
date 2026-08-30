const Kr="modulepreload",Gr=function(e){return"/build/"+e},Kt={},be=function(t,n,r){let i=Promise.resolve();if(n&&n.length>0){let o=function(l){return Promise.all(l.map(d=>Promise.resolve(d).then(f=>({status:"fulfilled",value:f}),f=>({status:"rejected",reason:f}))))};document.getElementsByTagName("link");const a=document.querySelector("meta[property=csp-nonce]"),c=(a==null?void 0:a.nonce)||(a==null?void 0:a.getAttribute("nonce"));i=o(n.map(l=>{if(l=Gr(l),l in Kt)return;Kt[l]=!0;const d=l.endsWith(".css"),f=d?'[rel="stylesheet"]':"";if(document.querySelector(`link[href="${l}"]${f}`))return;const p=document.createElement("link");if(p.rel=d?"stylesheet":Kr,d||(p.as="script"),p.crossOrigin="",p.href=l,c&&p.setAttribute("nonce",c),document.head.appendChild(p),d)return new Promise((y,h)=>{p.addEventListener("load",y),p.addEventListener("error",()=>h(new Error(`Unable to preload CSS for ${l}`)))})}))}function s(o){const a=new Event("vite:preloadError",{cancelable:!0});if(a.payload=o,window.dispatchEvent(a),!a.defaultPrevented)throw o}return i.then(o=>{for(const a of o||[])a.status==="rejected"&&s(a.reason);return t().catch(s)})};function $n(e,t){return function(){return e.apply(t,arguments)}}const{toString:Jr}=Object.prototype,{getPrototypeOf:Rt}=Object,{iterator:He,toStringTag:Un}=Symbol,Ve=(e=>t=>{const n=Jr.call(t);return e[n]||(e[n]=n.slice(8,-1).toLowerCase())})(Object.create(null)),q=e=>(e=e.toLowerCase(),t=>Ve(t)===e),qe=e=>t=>typeof t===e,{isArray:Ee}=Array,we=qe("undefined");function Ce(e){return e!==null&&!we(e)&&e.constructor!==null&&!we(e.constructor)&&L(e.constructor.isBuffer)&&e.constructor.isBuffer(e)}const jn=q("ArrayBuffer");function Xr(e){let t;return typeof ArrayBuffer<"u"&&ArrayBuffer.isView?t=ArrayBuffer.isView(e):t=e&&e.buffer&&jn(e.buffer),t}const Yr=qe("string"),L=qe("function"),Hn=qe("number"),Re=e=>e!==null&&typeof e=="object",Qr=e=>e===!0||e===!1,Be=e=>{if(Ve(e)!=="object")return!1;const t=Rt(e);return(t===null||t===Object.prototype||Object.getPrototypeOf(t)===null)&&!(Un in e)&&!(He in e)},Zr=e=>{if(!Re(e)||Ce(e))return!1;try{return Object.keys(e).length===0&&Object.getPrototypeOf(e)===Object.prototype}catch{return!1}},ei=q("Date"),ti=q("File"),ni=q("Blob"),ri=q("FileList"),ii=e=>Re(e)&&L(e.pipe),si=e=>{let t;return e&&(typeof FormData=="function"&&e instanceof FormData||L(e.append)&&((t=Ve(e))==="formdata"||t==="object"&&L(e.toString)&&e.toString()==="[object FormData]"))},oi=q("URLSearchParams"),[ai,ci,li,ui]=["ReadableStream","Request","Response","Headers"].map(q),di=e=>e.trim?e.trim():e.replace(/^[\s\uFEFF\xA0]+|[\s\uFEFF\xA0]+$/g,"");function De(e,t,{allOwnKeys:n=!1}={}){if(e===null||typeof e>"u")return;let r,i;if(typeof e!="object"&&(e=[e]),Ee(e))for(r=0,i=e.length;r<i;r++)t.call(null,e[r],r,e);else{if(Ce(e))return;const s=n?Object.getOwnPropertyNames(e):Object.keys(e),o=s.length;let a;for(r=0;r<o;r++)a=s[r],t.call(null,e[a],a,e)}}function Vn(e,t){if(Ce(e))return null;t=t.toLowerCase();const n=Object.keys(e);let r=n.length,i;for(;r-- >0;)if(i=n[r],t===i.toLowerCase())return i;return null}const oe=typeof globalThis<"u"?globalThis:typeof self<"u"?self:typeof window<"u"?window:global,qn=e=>!we(e)&&e!==oe;function pt(){const{caseless:e,skipUndefined:t}=qn(this)&&this||{},n={},r=(i,s)=>{const o=e&&Vn(n,s)||s;Be(n[o])&&Be(i)?n[o]=pt(n[o],i):Be(i)?n[o]=pt({},i):Ee(i)?n[o]=i.slice():(!t||!we(i))&&(n[o]=i)};for(let i=0,s=arguments.length;i<s;i++)arguments[i]&&De(arguments[i],r);return n}const fi=(e,t,n,{allOwnKeys:r}={})=>(De(t,(i,s)=>{n&&L(i)?e[s]=$n(i,n):e[s]=i},{allOwnKeys:r}),e),hi=e=>(e.charCodeAt(0)===65279&&(e=e.slice(1)),e),pi=(e,t,n,r)=>{e.prototype=Object.create(t.prototype,r),e.prototype.constructor=e,Object.defineProperty(e,"super",{value:t.prototype}),n&&Object.assign(e.prototype,n)},mi=(e,t,n,r)=>{let i,s,o;const a={};if(t=t||{},e==null)return t;do{for(i=Object.getOwnPropertyNames(e),s=i.length;s-- >0;)o=i[s],(!r||r(o,e,t))&&!a[o]&&(t[o]=e[o],a[o]=!0);e=n!==!1&&Rt(e)}while(e&&(!n||n(e,t))&&e!==Object.prototype);return t},gi=(e,t,n)=>{e=String(e),(n===void 0||n>e.length)&&(n=e.length),n-=t.length;const r=e.indexOf(t,n);return r!==-1&&r===n},wi=e=>{if(!e)return null;if(Ee(e))return e;let t=e.length;if(!Hn(t))return null;const n=new Array(t);for(;t-- >0;)n[t]=e[t];return n},yi=(e=>t=>e&&t instanceof e)(typeof Uint8Array<"u"&&Rt(Uint8Array)),bi=(e,t)=>{const r=(e&&e[He]).call(e);let i;for(;(i=r.next())&&!i.done;){const s=i.value;t.call(e,s[0],s[1])}},Ei=(e,t)=>{let n;const r=[];for(;(n=e.exec(t))!==null;)r.push(n);return r},Si=q("HTMLFormElement"),Ai=e=>e.toLowerCase().replace(/[-_\s]([a-z\d])(\w*)/g,function(n,r,i){return r.toUpperCase()+i}),Gt=(({hasOwnProperty:e})=>(t,n)=>e.call(t,n))(Object.prototype),Ii=q("RegExp"),Wn=(e,t)=>{const n=Object.getOwnPropertyDescriptors(e),r={};De(n,(i,s)=>{let o;(o=t(i,s,e))!==!1&&(r[s]=o||i)}),Object.defineProperties(e,r)},vi=e=>{Wn(e,(t,n)=>{if(L(e)&&["arguments","caller","callee"].indexOf(n)!==-1)return!1;const r=e[n];if(L(r)){if(t.enumerable=!1,"writable"in t){t.writable=!1;return}t.set||(t.set=()=>{throw Error("Can not rewrite read-only method '"+n+"'")})}})},_i=(e,t)=>{const n={},r=i=>{i.forEach(s=>{n[s]=!0})};return Ee(e)?r(e):r(String(e).split(t)),n},Ti=()=>{},Ci=(e,t)=>e!=null&&Number.isFinite(e=+e)?e:t;function Ri(e){return!!(e&&L(e.append)&&e[Un]==="FormData"&&e[He])}const Di=e=>{const t=new Array(10),n=(r,i)=>{if(Re(r)){if(t.indexOf(r)>=0)return;if(Ce(r))return r;if(!("toJSON"in r)){t[i]=r;const s=Ee(r)?[]:{};return De(r,(o,a)=>{const c=n(o,i+1);!we(c)&&(s[a]=c)}),t[i]=void 0,s}}return r};return n(e,0)},Pi=q("AsyncFunction"),Oi=e=>e&&(Re(e)||L(e))&&L(e.then)&&L(e.catch),zn=((e,t)=>e?setImmediate:t?((n,r)=>(oe.addEventListener("message",({source:i,data:s})=>{i===oe&&s===n&&r.length&&r.shift()()},!1),i=>{r.push(i),oe.postMessage(n,"*")}))(`axios@${Math.random()}`,[]):n=>setTimeout(n))(typeof setImmediate=="function",L(oe.postMessage)),Fi=typeof queueMicrotask<"u"?queueMicrotask.bind(oe):typeof process<"u"&&process.nextTick||zn,ki=e=>e!=null&&L(e[He]),u={isArray:Ee,isArrayBuffer:jn,isBuffer:Ce,isFormData:si,isArrayBufferView:Xr,isString:Yr,isNumber:Hn,isBoolean:Qr,isObject:Re,isPlainObject:Be,isEmptyObject:Zr,isReadableStream:ai,isRequest:ci,isResponse:li,isHeaders:ui,isUndefined:we,isDate:ei,isFile:ti,isBlob:ni,isRegExp:Ii,isFunction:L,isStream:ii,isURLSearchParams:oi,isTypedArray:yi,isFileList:ri,forEach:De,merge:pt,extend:fi,trim:di,stripBOM:hi,inherits:pi,toFlatObject:mi,kindOf:Ve,kindOfTest:q,endsWith:gi,toArray:wi,forEachEntry:bi,matchAll:Ei,isHTMLForm:Si,hasOwnProperty:Gt,hasOwnProp:Gt,reduceDescriptors:Wn,freezeMethods:vi,toObjectSet:_i,toCamelCase:Ai,noop:Ti,toFiniteNumber:Ci,findKey:Vn,global:oe,isContextDefined:qn,isSpecCompliantForm:Ri,toJSONObject:Di,isAsyncFn:Pi,isThenable:Oi,setImmediate:zn,asap:Fi,isIterable:ki};function w(e,t,n,r,i){Error.call(this),Error.captureStackTrace?Error.captureStackTrace(this,this.constructor):this.stack=new Error().stack,this.message=e,this.name="AxiosError",t&&(this.code=t),n&&(this.config=n),r&&(this.request=r),i&&(this.response=i,this.status=i.status?i.status:null)}u.inherits(w,Error,{toJSON:function(){return{message:this.message,name:this.name,description:this.description,number:this.number,fileName:this.fileName,lineNumber:this.lineNumber,columnNumber:this.columnNumber,stack:this.stack,config:u.toJSONObject(this.config),code:this.code,status:this.status}}});const Kn=w.prototype,Gn={};["ERR_BAD_OPTION_VALUE","ERR_BAD_OPTION","ECONNABORTED","ETIMEDOUT","ERR_NETWORK","ERR_FR_TOO_MANY_REDIRECTS","ERR_DEPRECATED","ERR_BAD_RESPONSE","ERR_BAD_REQUEST","ERR_CANCELED","ERR_NOT_SUPPORT","ERR_INVALID_URL"].forEach(e=>{Gn[e]={value:e}});Object.defineProperties(w,Gn);Object.defineProperty(Kn,"isAxiosError",{value:!0});w.from=(e,t,n,r,i,s)=>{const o=Object.create(Kn);u.toFlatObject(e,o,function(d){return d!==Error.prototype},l=>l!=="isAxiosError");const a=e&&e.message?e.message:"Error",c=t==null&&e?e.code:t;return w.call(o,a,c,n,r,i),e&&o.cause==null&&Object.defineProperty(o,"cause",{value:e,configurable:!0}),o.name=e&&e.name||"Error",s&&Object.assign(o,s),o};const Li=null;function mt(e){return u.isPlainObject(e)||u.isArray(e)}function Jn(e){return u.endsWith(e,"[]")?e.slice(0,-2):e}function Jt(e,t,n){return e?e.concat(t).map(function(i,s){return i=Jn(i),!n&&s?"["+i+"]":i}).join(n?".":""):t}function Bi(e){return u.isArray(e)&&!e.some(mt)}const Ni=u.toFlatObject(u,{},null,function(t){return/^is[A-Z]/.test(t)});function We(e,t,n){if(!u.isObject(e))throw new TypeError("target must be an object");t=t||new FormData,n=u.toFlatObject(n,{metaTokens:!0,dots:!1,indexes:!1},!1,function(g,m){return!u.isUndefined(m[g])});const r=n.metaTokens,i=n.visitor||d,s=n.dots,o=n.indexes,c=(n.Blob||typeof Blob<"u"&&Blob)&&u.isSpecCompliantForm(t);if(!u.isFunction(i))throw new TypeError("visitor must be a function");function l(h){if(h===null)return"";if(u.isDate(h))return h.toISOString();if(u.isBoolean(h))return h.toString();if(!c&&u.isBlob(h))throw new w("Blob is not supported. Use a Buffer instead.");return u.isArrayBuffer(h)||u.isTypedArray(h)?c&&typeof Blob=="function"?new Blob([h]):Buffer.from(h):h}function d(h,g,m){let S=h;if(h&&!m&&typeof h=="object"){if(u.endsWith(g,"{}"))g=r?g:g.slice(0,-2),h=JSON.stringify(h);else if(u.isArray(h)&&Bi(h)||(u.isFileList(h)||u.endsWith(g,"[]"))&&(S=u.toArray(h)))return g=Jn(g),S.forEach(function(E,T){!(u.isUndefined(E)||E===null)&&t.append(o===!0?Jt([g],T,s):o===null?g:g+"[]",l(E))}),!1}return mt(h)?!0:(t.append(Jt(m,g,s),l(h)),!1)}const f=[],p=Object.assign(Ni,{defaultVisitor:d,convertValue:l,isVisitable:mt});function y(h,g){if(!u.isUndefined(h)){if(f.indexOf(h)!==-1)throw Error("Circular reference detected in "+g.join("."));f.push(h),u.forEach(h,function(S,F){(!(u.isUndefined(S)||S===null)&&i.call(t,S,u.isString(F)?F.trim():F,g,p))===!0&&y(S,g?g.concat(F):[F])}),f.pop()}}if(!u.isObject(e))throw new TypeError("data must be an object");return y(e),t}function Xt(e){const t={"!":"%21","'":"%27","(":"%28",")":"%29","~":"%7E","%20":"+","%00":"\0"};return encodeURIComponent(e).replace(/[!'()~]|%20|%00/g,function(r){return t[r]})}function Dt(e,t){this._pairs=[],e&&We(e,this,t)}const Xn=Dt.prototype;Xn.append=function(t,n){this._pairs.push([t,n])};Xn.toString=function(t){const n=t?function(r){return t.call(this,r,Xt)}:Xt;return this._pairs.map(function(i){return n(i[0])+"="+n(i[1])},"").join("&")};function xi(e){return encodeURIComponent(e).replace(/%3A/gi,":").replace(/%24/g,"$").replace(/%2C/gi,",").replace(/%20/g,"+")}function Yn(e,t,n){if(!t)return e;const r=n&&n.encode||xi;u.isFunction(n)&&(n={serialize:n});const i=n&&n.serialize;let s;if(i?s=i(t,n):s=u.isURLSearchParams(t)?t.toString():new Dt(t,n).toString(r),s){const o=e.indexOf("#");o!==-1&&(e=e.slice(0,o)),e+=(e.indexOf("?")===-1?"?":"&")+s}return e}class Yt{constructor(){this.handlers=[]}use(t,n,r){return this.handlers.push({fulfilled:t,rejected:n,synchronous:r?r.synchronous:!1,runWhen:r?r.runWhen:null}),this.handlers.length-1}eject(t){this.handlers[t]&&(this.handlers[t]=null)}clear(){this.handlers&&(this.handlers=[])}forEach(t){u.forEach(this.handlers,function(r){r!==null&&t(r)})}}const Qn={silentJSONParsing:!0,forcedJSONParsing:!0,clarifyTimeoutError:!1},Mi=typeof URLSearchParams<"u"?URLSearchParams:Dt,$i=typeof FormData<"u"?FormData:null,Ui=typeof Blob<"u"?Blob:null,ji={isBrowser:!0,classes:{URLSearchParams:Mi,FormData:$i,Blob:Ui},protocols:["http","https","file","blob","url","data"]},Pt=typeof window<"u"&&typeof document<"u",gt=typeof navigator=="object"&&navigator||void 0,Hi=Pt&&(!gt||["ReactNative","NativeScript","NS"].indexOf(gt.product)<0),Vi=typeof WorkerGlobalScope<"u"&&self instanceof WorkerGlobalScope&&typeof self.importScripts=="function",qi=Pt&&window.location.href||"http://localhost",Wi=Object.freeze(Object.defineProperty({__proto__:null,hasBrowserEnv:Pt,hasStandardBrowserEnv:Hi,hasStandardBrowserWebWorkerEnv:Vi,navigator:gt,origin:qi},Symbol.toStringTag,{value:"Module"})),P={...Wi,...ji};function zi(e,t){return We(e,new P.classes.URLSearchParams,{visitor:function(n,r,i,s){return P.isNode&&u.isBuffer(n)?(this.append(r,n.toString("base64")),!1):s.defaultVisitor.apply(this,arguments)},...t})}function Ki(e){return u.matchAll(/\w+|\[(\w*)]/g,e).map(t=>t[0]==="[]"?"":t[1]||t[0])}function Gi(e){const t={},n=Object.keys(e);let r;const i=n.length;let s;for(r=0;r<i;r++)s=n[r],t[s]=e[s];return t}function Zn(e){function t(n,r,i,s){let o=n[s++];if(o==="__proto__")return!0;const a=Number.isFinite(+o),c=s>=n.length;return o=!o&&u.isArray(i)?i.length:o,c?(u.hasOwnProp(i,o)?i[o]=[i[o],r]:i[o]=r,!a):((!i[o]||!u.isObject(i[o]))&&(i[o]=[]),t(n,r,i[o],s)&&u.isArray(i[o])&&(i[o]=Gi(i[o])),!a)}if(u.isFormData(e)&&u.isFunction(e.entries)){const n={};return u.forEachEntry(e,(r,i)=>{t(Ki(r),i,n,0)}),n}return null}function Ji(e,t,n){if(u.isString(e))try{return(t||JSON.parse)(e),u.trim(e)}catch(r){if(r.name!=="SyntaxError")throw r}return(n||JSON.stringify)(e)}const Pe={transitional:Qn,adapter:["xhr","http","fetch"],transformRequest:[function(t,n){const r=n.getContentType()||"",i=r.indexOf("application/json")>-1,s=u.isObject(t);if(s&&u.isHTMLForm(t)&&(t=new FormData(t)),u.isFormData(t))return i?JSON.stringify(Zn(t)):t;if(u.isArrayBuffer(t)||u.isBuffer(t)||u.isStream(t)||u.isFile(t)||u.isBlob(t)||u.isReadableStream(t))return t;if(u.isArrayBufferView(t))return t.buffer;if(u.isURLSearchParams(t))return n.setContentType("application/x-www-form-urlencoded;charset=utf-8",!1),t.toString();let a;if(s){if(r.indexOf("application/x-www-form-urlencoded")>-1)return zi(t,this.formSerializer).toString();if((a=u.isFileList(t))||r.indexOf("multipart/form-data")>-1){const c=this.env&&this.env.FormData;return We(a?{"files[]":t}:t,c&&new c,this.formSerializer)}}return s||i?(n.setContentType("application/json",!1),Ji(t)):t}],transformResponse:[function(t){const n=this.transitional||Pe.transitional,r=n&&n.forcedJSONParsing,i=this.responseType==="json";if(u.isResponse(t)||u.isReadableStream(t))return t;if(t&&u.isString(t)&&(r&&!this.responseType||i)){const o=!(n&&n.silentJSONParsing)&&i;try{return JSON.parse(t,this.parseReviver)}catch(a){if(o)throw a.name==="SyntaxError"?w.from(a,w.ERR_BAD_RESPONSE,this,null,this.response):a}}return t}],timeout:0,xsrfCookieName:"XSRF-TOKEN",xsrfHeaderName:"X-XSRF-TOKEN",maxContentLength:-1,maxBodyLength:-1,env:{FormData:P.classes.FormData,Blob:P.classes.Blob},validateStatus:function(t){return t>=200&&t<300},headers:{common:{Accept:"application/json, text/plain, */*","Content-Type":void 0}}};u.forEach(["delete","get","head","post","put","patch"],e=>{Pe.headers[e]={}});const Xi=u.toObjectSet(["age","authorization","content-length","content-type","etag","expires","from","host","if-modified-since","if-unmodified-since","last-modified","location","max-forwards","proxy-authorization","referer","retry-after","user-agent"]),Yi=e=>{const t={};let n,r,i;return e&&e.split(`
`).forEach(function(o){i=o.indexOf(":"),n=o.substring(0,i).trim().toLowerCase(),r=o.substring(i+1).trim(),!(!n||t[n]&&Xi[n])&&(n==="set-cookie"?t[n]?t[n].push(r):t[n]=[r]:t[n]=t[n]?t[n]+", "+r:r)}),t},Qt=Symbol("internals");function Ae(e){return e&&String(e).trim().toLowerCase()}function Ne(e){return e===!1||e==null?e:u.isArray(e)?e.map(Ne):String(e)}function Qi(e){const t=Object.create(null),n=/([^\s,;=]+)\s*(?:=\s*([^,;]+))?/g;let r;for(;r=n.exec(e);)t[r[1]]=r[2];return t}const Zi=e=>/^[-_a-zA-Z0-9^`|~,!#$%&'*+.]+$/.test(e.trim());function Qe(e,t,n,r,i){if(u.isFunction(r))return r.call(this,t,n);if(i&&(t=n),!!u.isString(t)){if(u.isString(r))return t.indexOf(r)!==-1;if(u.isRegExp(r))return r.test(t)}}function es(e){return e.trim().toLowerCase().replace(/([a-z\d])(\w*)/g,(t,n,r)=>n.toUpperCase()+r)}function ts(e,t){const n=u.toCamelCase(" "+t);["get","set","has"].forEach(r=>{Object.defineProperty(e,r+n,{value:function(i,s,o){return this[r].call(this,t,i,s,o)},configurable:!0})})}let B=class{constructor(t){t&&this.set(t)}set(t,n,r){const i=this;function s(a,c,l){const d=Ae(c);if(!d)throw new Error("header name must be a non-empty string");const f=u.findKey(i,d);(!f||i[f]===void 0||l===!0||l===void 0&&i[f]!==!1)&&(i[f||c]=Ne(a))}const o=(a,c)=>u.forEach(a,(l,d)=>s(l,d,c));if(u.isPlainObject(t)||t instanceof this.constructor)o(t,n);else if(u.isString(t)&&(t=t.trim())&&!Zi(t))o(Yi(t),n);else if(u.isObject(t)&&u.isIterable(t)){let a={},c,l;for(const d of t){if(!u.isArray(d))throw TypeError("Object iterator must return a key-value pair");a[l=d[0]]=(c=a[l])?u.isArray(c)?[...c,d[1]]:[c,d[1]]:d[1]}o(a,n)}else t!=null&&s(n,t,r);return this}get(t,n){if(t=Ae(t),t){const r=u.findKey(this,t);if(r){const i=this[r];if(!n)return i;if(n===!0)return Qi(i);if(u.isFunction(n))return n.call(this,i,r);if(u.isRegExp(n))return n.exec(i);throw new TypeError("parser must be boolean|regexp|function")}}}has(t,n){if(t=Ae(t),t){const r=u.findKey(this,t);return!!(r&&this[r]!==void 0&&(!n||Qe(this,this[r],r,n)))}return!1}delete(t,n){const r=this;let i=!1;function s(o){if(o=Ae(o),o){const a=u.findKey(r,o);a&&(!n||Qe(r,r[a],a,n))&&(delete r[a],i=!0)}}return u.isArray(t)?t.forEach(s):s(t),i}clear(t){const n=Object.keys(this);let r=n.length,i=!1;for(;r--;){const s=n[r];(!t||Qe(this,this[s],s,t,!0))&&(delete this[s],i=!0)}return i}normalize(t){const n=this,r={};return u.forEach(this,(i,s)=>{const o=u.findKey(r,s);if(o){n[o]=Ne(i),delete n[s];return}const a=t?es(s):String(s).trim();a!==s&&delete n[s],n[a]=Ne(i),r[a]=!0}),this}concat(...t){return this.constructor.concat(this,...t)}toJSON(t){const n=Object.create(null);return u.forEach(this,(r,i)=>{r!=null&&r!==!1&&(n[i]=t&&u.isArray(r)?r.join(", "):r)}),n}[Symbol.iterator](){return Object.entries(this.toJSON())[Symbol.iterator]()}toString(){return Object.entries(this.toJSON()).map(([t,n])=>t+": "+n).join(`
`)}getSetCookie(){return this.get("set-cookie")||[]}get[Symbol.toStringTag](){return"AxiosHeaders"}static from(t){return t instanceof this?t:new this(t)}static concat(t,...n){const r=new this(t);return n.forEach(i=>r.set(i)),r}static accessor(t){const r=(this[Qt]=this[Qt]={accessors:{}}).accessors,i=this.prototype;function s(o){const a=Ae(o);r[a]||(ts(i,o),r[a]=!0)}return u.isArray(t)?t.forEach(s):s(t),this}};B.accessor(["Content-Type","Content-Length","Accept","Accept-Encoding","User-Agent","Authorization"]);u.reduceDescriptors(B.prototype,({value:e},t)=>{let n=t[0].toUpperCase()+t.slice(1);return{get:()=>e,set(r){this[n]=r}}});u.freezeMethods(B);function Ze(e,t){const n=this||Pe,r=t||n,i=B.from(r.headers);let s=r.data;return u.forEach(e,function(a){s=a.call(n,s,i.normalize(),t?t.status:void 0)}),i.normalize(),s}function er(e){return!!(e&&e.__CANCEL__)}function Se(e,t,n){w.call(this,e??"canceled",w.ERR_CANCELED,t,n),this.name="CanceledError"}u.inherits(Se,w,{__CANCEL__:!0});function tr(e,t,n){const r=n.config.validateStatus;!n.status||!r||r(n.status)?e(n):t(new w("Request failed with status code "+n.status,[w.ERR_BAD_REQUEST,w.ERR_BAD_RESPONSE][Math.floor(n.status/100)-4],n.config,n.request,n))}function ns(e){const t=/^([-+\w]{1,25})(:?\/\/|:)/.exec(e);return t&&t[1]||""}function rs(e,t){e=e||10;const n=new Array(e),r=new Array(e);let i=0,s=0,o;return t=t!==void 0?t:1e3,function(c){const l=Date.now(),d=r[s];o||(o=l),n[i]=c,r[i]=l;let f=s,p=0;for(;f!==i;)p+=n[f++],f=f%e;if(i=(i+1)%e,i===s&&(s=(s+1)%e),l-o<t)return;const y=d&&l-d;return y?Math.round(p*1e3/y):void 0}}function is(e,t){let n=0,r=1e3/t,i,s;const o=(l,d=Date.now())=>{n=d,i=null,s&&(clearTimeout(s),s=null),e(...l)};return[(...l)=>{const d=Date.now(),f=d-n;f>=r?o(l,d):(i=l,s||(s=setTimeout(()=>{s=null,o(i)},r-f)))},()=>i&&o(i)]}const Me=(e,t,n=3)=>{let r=0;const i=rs(50,250);return is(s=>{const o=s.loaded,a=s.lengthComputable?s.total:void 0,c=o-r,l=i(c),d=o<=a;r=o;const f={loaded:o,total:a,progress:a?o/a:void 0,bytes:c,rate:l||void 0,estimated:l&&a&&d?(a-o)/l:void 0,event:s,lengthComputable:a!=null,[t?"download":"upload"]:!0};e(f)},n)},Zt=(e,t)=>{const n=e!=null;return[r=>t[0]({lengthComputable:n,total:e,loaded:r}),t[1]]},en=e=>(...t)=>u.asap(()=>e(...t)),ss=P.hasStandardBrowserEnv?((e,t)=>n=>(n=new URL(n,P.origin),e.protocol===n.protocol&&e.host===n.host&&(t||e.port===n.port)))(new URL(P.origin),P.navigator&&/(msie|trident)/i.test(P.navigator.userAgent)):()=>!0,os=P.hasStandardBrowserEnv?{write(e,t,n,r,i,s){const o=[e+"="+encodeURIComponent(t)];u.isNumber(n)&&o.push("expires="+new Date(n).toGMTString()),u.isString(r)&&o.push("path="+r),u.isString(i)&&o.push("domain="+i),s===!0&&o.push("secure"),document.cookie=o.join("; ")},read(e){const t=document.cookie.match(new RegExp("(^|;\\s*)("+e+")=([^;]*)"));return t?decodeURIComponent(t[3]):null},remove(e){this.write(e,"",Date.now()-864e5)}}:{write(){},read(){return null},remove(){}};function as(e){return/^([a-z][a-z\d+\-.]*:)?\/\//i.test(e)}function cs(e,t){return t?e.replace(/\/?\/$/,"")+"/"+t.replace(/^\/+/,""):e}function nr(e,t,n){let r=!as(t);return e&&(r||n==!1)?cs(e,t):t}const tn=e=>e instanceof B?{...e}:e;function ue(e,t){t=t||{};const n={};function r(l,d,f,p){return u.isPlainObject(l)&&u.isPlainObject(d)?u.merge.call({caseless:p},l,d):u.isPlainObject(d)?u.merge({},d):u.isArray(d)?d.slice():d}function i(l,d,f,p){if(u.isUndefined(d)){if(!u.isUndefined(l))return r(void 0,l,f,p)}else return r(l,d,f,p)}function s(l,d){if(!u.isUndefined(d))return r(void 0,d)}function o(l,d){if(u.isUndefined(d)){if(!u.isUndefined(l))return r(void 0,l)}else return r(void 0,d)}function a(l,d,f){if(f in t)return r(l,d);if(f in e)return r(void 0,l)}const c={url:s,method:s,data:s,baseURL:o,transformRequest:o,transformResponse:o,paramsSerializer:o,timeout:o,timeoutMessage:o,withCredentials:o,withXSRFToken:o,adapter:o,responseType:o,xsrfCookieName:o,xsrfHeaderName:o,onUploadProgress:o,onDownloadProgress:o,decompress:o,maxContentLength:o,maxBodyLength:o,beforeRedirect:o,transport:o,httpAgent:o,httpsAgent:o,cancelToken:o,socketPath:o,responseEncoding:o,validateStatus:a,headers:(l,d,f)=>i(tn(l),tn(d),f,!0)};return u.forEach(Object.keys({...e,...t}),function(d){const f=c[d]||i,p=f(e[d],t[d],d);u.isUndefined(p)&&f!==a||(n[d]=p)}),n}const rr=e=>{const t=ue({},e);let{data:n,withXSRFToken:r,xsrfHeaderName:i,xsrfCookieName:s,headers:o,auth:a}=t;if(t.headers=o=B.from(o),t.url=Yn(nr(t.baseURL,t.url,t.allowAbsoluteUrls),e.params,e.paramsSerializer),a&&o.set("Authorization","Basic "+btoa((a.username||"")+":"+(a.password?unescape(encodeURIComponent(a.password)):""))),u.isFormData(n)){if(P.hasStandardBrowserEnv||P.hasStandardBrowserWebWorkerEnv)o.setContentType(void 0);else if(u.isFunction(n.getHeaders)){const c=n.getHeaders(),l=["content-type","content-length"];Object.entries(c).forEach(([d,f])=>{l.includes(d.toLowerCase())&&o.set(d,f)})}}if(P.hasStandardBrowserEnv&&(r&&u.isFunction(r)&&(r=r(t)),r||r!==!1&&ss(t.url))){const c=i&&s&&os.read(s);c&&o.set(i,c)}return t},ls=typeof XMLHttpRequest<"u",us=ls&&function(e){return new Promise(function(n,r){const i=rr(e);let s=i.data;const o=B.from(i.headers).normalize();let{responseType:a,onUploadProgress:c,onDownloadProgress:l}=i,d,f,p,y,h;function g(){y&&y(),h&&h(),i.cancelToken&&i.cancelToken.unsubscribe(d),i.signal&&i.signal.removeEventListener("abort",d)}let m=new XMLHttpRequest;m.open(i.method.toUpperCase(),i.url,!0),m.timeout=i.timeout;function S(){if(!m)return;const E=B.from("getAllResponseHeaders"in m&&m.getAllResponseHeaders()),k={data:!a||a==="text"||a==="json"?m.responseText:m.response,status:m.status,statusText:m.statusText,headers:E,config:e,request:m};tr(function(R){n(R),g()},function(R){r(R),g()},k),m=null}"onloadend"in m?m.onloadend=S:m.onreadystatechange=function(){!m||m.readyState!==4||m.status===0&&!(m.responseURL&&m.responseURL.indexOf("file:")===0)||setTimeout(S)},m.onabort=function(){m&&(r(new w("Request aborted",w.ECONNABORTED,e,m)),m=null)},m.onerror=function(T){const k=T&&T.message?T.message:"Network Error",K=new w(k,w.ERR_NETWORK,e,m);K.event=T||null,r(K),m=null},m.ontimeout=function(){let T=i.timeout?"timeout of "+i.timeout+"ms exceeded":"timeout exceeded";const k=i.transitional||Qn;i.timeoutErrorMessage&&(T=i.timeoutErrorMessage),r(new w(T,k.clarifyTimeoutError?w.ETIMEDOUT:w.ECONNABORTED,e,m)),m=null},s===void 0&&o.setContentType(null),"setRequestHeader"in m&&u.forEach(o.toJSON(),function(T,k){m.setRequestHeader(k,T)}),u.isUndefined(i.withCredentials)||(m.withCredentials=!!i.withCredentials),a&&a!=="json"&&(m.responseType=i.responseType),l&&([p,h]=Me(l,!0),m.addEventListener("progress",p)),c&&m.upload&&([f,y]=Me(c),m.upload.addEventListener("progress",f),m.upload.addEventListener("loadend",y)),(i.cancelToken||i.signal)&&(d=E=>{m&&(r(!E||E.type?new Se(null,e,m):E),m.abort(),m=null)},i.cancelToken&&i.cancelToken.subscribe(d),i.signal&&(i.signal.aborted?d():i.signal.addEventListener("abort",d)));const F=ns(i.url);if(F&&P.protocols.indexOf(F)===-1){r(new w("Unsupported protocol "+F+":",w.ERR_BAD_REQUEST,e));return}m.send(s||null)})},ds=(e,t)=>{const{length:n}=e=e?e.filter(Boolean):[];if(t||n){let r=new AbortController,i;const s=function(l){if(!i){i=!0,a();const d=l instanceof Error?l:this.reason;r.abort(d instanceof w?d:new Se(d instanceof Error?d.message:d))}};let o=t&&setTimeout(()=>{o=null,s(new w(`timeout ${t} of ms exceeded`,w.ETIMEDOUT))},t);const a=()=>{e&&(o&&clearTimeout(o),o=null,e.forEach(l=>{l.unsubscribe?l.unsubscribe(s):l.removeEventListener("abort",s)}),e=null)};e.forEach(l=>l.addEventListener("abort",s));const{signal:c}=r;return c.unsubscribe=()=>u.asap(a),c}},fs=function*(e,t){let n=e.byteLength;if(n<t){yield e;return}let r=0,i;for(;r<n;)i=r+t,yield e.slice(r,i),r=i},hs=async function*(e,t){for await(const n of ps(e))yield*fs(n,t)},ps=async function*(e){if(e[Symbol.asyncIterator]){yield*e;return}const t=e.getReader();try{for(;;){const{done:n,value:r}=await t.read();if(n)break;yield r}}finally{await t.cancel()}},nn=(e,t,n,r)=>{const i=hs(e,t);let s=0,o,a=c=>{o||(o=!0,r&&r(c))};return new ReadableStream({async pull(c){try{const{done:l,value:d}=await i.next();if(l){a(),c.close();return}let f=d.byteLength;if(n){let p=s+=f;n(p)}c.enqueue(new Uint8Array(d))}catch(l){throw a(l),l}},cancel(c){return a(c),i.return()}},{highWaterMark:2})},rn=64*1024,{isFunction:Fe}=u,ms=(({Request:e,Response:t})=>({Request:e,Response:t}))(u.global),{ReadableStream:sn,TextEncoder:on}=u.global,an=(e,...t)=>{try{return!!e(...t)}catch{return!1}},gs=e=>{e=u.merge.call({skipUndefined:!0},ms,e);const{fetch:t,Request:n,Response:r}=e,i=t?Fe(t):typeof fetch=="function",s=Fe(n),o=Fe(r);if(!i)return!1;const a=i&&Fe(sn),c=i&&(typeof on=="function"?(h=>g=>h.encode(g))(new on):async h=>new Uint8Array(await new n(h).arrayBuffer())),l=s&&a&&an(()=>{let h=!1;const g=new n(P.origin,{body:new sn,method:"POST",get duplex(){return h=!0,"half"}}).headers.has("Content-Type");return h&&!g}),d=o&&a&&an(()=>u.isReadableStream(new r("").body)),f={stream:d&&(h=>h.body)};i&&["text","arrayBuffer","blob","formData","stream"].forEach(h=>{!f[h]&&(f[h]=(g,m)=>{let S=g&&g[h];if(S)return S.call(g);throw new w(`Response type '${h}' is not supported`,w.ERR_NOT_SUPPORT,m)})});const p=async h=>{if(h==null)return 0;if(u.isBlob(h))return h.size;if(u.isSpecCompliantForm(h))return(await new n(P.origin,{method:"POST",body:h}).arrayBuffer()).byteLength;if(u.isArrayBufferView(h)||u.isArrayBuffer(h))return h.byteLength;if(u.isURLSearchParams(h)&&(h=h+""),u.isString(h))return(await c(h)).byteLength},y=async(h,g)=>{const m=u.toFiniteNumber(h.getContentLength());return m??p(g)};return async h=>{let{url:g,method:m,data:S,signal:F,cancelToken:E,timeout:T,onDownloadProgress:k,onUploadProgress:K,responseType:R,headers:I,withCredentials:_="same-origin",fetchOptions:N}=rr(h),V=t||fetch;R=R?(R+"").toLowerCase():"text";let x=ds([F,E&&E.toAbortSignal()],T),C=null;const U=x&&x.unsubscribe&&(()=>{x.unsubscribe()});let re;try{if(K&&l&&m!=="get"&&m!=="head"&&(re=await y(I,S))!==0){let Q=new n(g,{method:"POST",body:S,duplex:"half"}),me;if(u.isFormData(S)&&(me=Q.headers.get("content-type"))&&I.setContentType(me),Q.body){const[Ye,Oe]=Zt(re,Me(en(K)));S=nn(Q.body,rn,Ye,Oe)}}u.isString(_)||(_=_?"include":"omit");const W=s&&"credentials"in n.prototype,qt={...N,signal:x,method:m.toUpperCase(),headers:I.normalize().toJSON(),body:S,duplex:"half",credentials:W?_:void 0};C=s&&new n(g,qt);let Y=await(s?V(C,N):V(g,qt));const Wt=d&&(R==="stream"||R==="response");if(d&&(k||Wt&&U)){const Q={};["status","statusText","headers"].forEach(zt=>{Q[zt]=Y[zt]});const me=u.toFiniteNumber(Y.headers.get("content-length")),[Ye,Oe]=k&&Zt(me,Me(en(k),!0))||[];Y=new r(nn(Y.body,rn,Ye,()=>{Oe&&Oe(),U&&U()}),Q)}R=R||"text";let zr=await f[u.findKey(f,R)||"text"](Y,h);return!Wt&&U&&U(),await new Promise((Q,me)=>{tr(Q,me,{data:zr,headers:B.from(Y.headers),status:Y.status,statusText:Y.statusText,config:h,request:C})})}catch(W){throw U&&U(),W&&W.name==="TypeError"&&/Load failed|fetch/i.test(W.message)?Object.assign(new w("Network Error",w.ERR_NETWORK,h,C),{cause:W.cause||W}):w.from(W,W&&W.code,h,C)}}},ws=new Map,ir=e=>{let t=e?e.env:{};const{fetch:n,Request:r,Response:i}=t,s=[r,i,n];let o=s.length,a=o,c,l,d=ws;for(;a--;)c=s[a],l=d.get(c),l===void 0&&d.set(c,l=a?new Map:gs(t)),d=l;return l};ir();const wt={http:Li,xhr:us,fetch:{get:ir}};u.forEach(wt,(e,t)=>{if(e){try{Object.defineProperty(e,"name",{value:t})}catch{}Object.defineProperty(e,"adapterName",{value:t})}});const cn=e=>`- ${e}`,ys=e=>u.isFunction(e)||e===null||e===!1,sr={getAdapter:(e,t)=>{e=u.isArray(e)?e:[e];const{length:n}=e;let r,i;const s={};for(let o=0;o<n;o++){r=e[o];let a;if(i=r,!ys(r)&&(i=wt[(a=String(r)).toLowerCase()],i===void 0))throw new w(`Unknown adapter '${a}'`);if(i&&(u.isFunction(i)||(i=i.get(t))))break;s[a||"#"+o]=i}if(!i){const o=Object.entries(s).map(([c,l])=>`adapter ${c} `+(l===!1?"is not supported by the environment":"is not available in the build"));let a=n?o.length>1?`since :
`+o.map(cn).join(`
`):" "+cn(o[0]):"as no adapter specified";throw new w("There is no suitable adapter to dispatch the request "+a,"ERR_NOT_SUPPORT")}return i},adapters:wt};function et(e){if(e.cancelToken&&e.cancelToken.throwIfRequested(),e.signal&&e.signal.aborted)throw new Se(null,e)}function ln(e){return et(e),e.headers=B.from(e.headers),e.data=Ze.call(e,e.transformRequest),["post","put","patch"].indexOf(e.method)!==-1&&e.headers.setContentType("application/x-www-form-urlencoded",!1),sr.getAdapter(e.adapter||Pe.adapter,e)(e).then(function(r){return et(e),r.data=Ze.call(e,e.transformResponse,r),r.headers=B.from(r.headers),r},function(r){return er(r)||(et(e),r&&r.response&&(r.response.data=Ze.call(e,e.transformResponse,r.response),r.response.headers=B.from(r.response.headers))),Promise.reject(r)})}const or="1.12.2",ze={};["object","boolean","number","function","string","symbol"].forEach((e,t)=>{ze[e]=function(r){return typeof r===e||"a"+(t<1?"n ":" ")+e}});const un={};ze.transitional=function(t,n,r){function i(s,o){return"[Axios v"+or+"] Transitional option '"+s+"'"+o+(r?". "+r:"")}return(s,o,a)=>{if(t===!1)throw new w(i(o," has been removed"+(n?" in "+n:"")),w.ERR_DEPRECATED);return n&&!un[o]&&(un[o]=!0,console.warn(i(o," has been deprecated since v"+n+" and will be removed in the near future"))),t?t(s,o,a):!0}};ze.spelling=function(t){return(n,r)=>(console.warn(`${r} is likely a misspelling of ${t}`),!0)};function bs(e,t,n){if(typeof e!="object")throw new w("options must be an object",w.ERR_BAD_OPTION_VALUE);const r=Object.keys(e);let i=r.length;for(;i-- >0;){const s=r[i],o=t[s];if(o){const a=e[s],c=a===void 0||o(a,s,e);if(c!==!0)throw new w("option "+s+" must be "+c,w.ERR_BAD_OPTION_VALUE);continue}if(n!==!0)throw new w("Unknown option "+s,w.ERR_BAD_OPTION)}}const xe={assertOptions:bs,validators:ze},z=xe.validators;let ce=class{constructor(t){this.defaults=t||{},this.interceptors={request:new Yt,response:new Yt}}async request(t,n){try{return await this._request(t,n)}catch(r){if(r instanceof Error){let i={};Error.captureStackTrace?Error.captureStackTrace(i):i=new Error;const s=i.stack?i.stack.replace(/^.+\n/,""):"";try{r.stack?s&&!String(r.stack).endsWith(s.replace(/^.+\n.+\n/,""))&&(r.stack+=`
`+s):r.stack=s}catch{}}throw r}}_request(t,n){typeof t=="string"?(n=n||{},n.url=t):n=t||{},n=ue(this.defaults,n);const{transitional:r,paramsSerializer:i,headers:s}=n;r!==void 0&&xe.assertOptions(r,{silentJSONParsing:z.transitional(z.boolean),forcedJSONParsing:z.transitional(z.boolean),clarifyTimeoutError:z.transitional(z.boolean)},!1),i!=null&&(u.isFunction(i)?n.paramsSerializer={serialize:i}:xe.assertOptions(i,{encode:z.function,serialize:z.function},!0)),n.allowAbsoluteUrls!==void 0||(this.defaults.allowAbsoluteUrls!==void 0?n.allowAbsoluteUrls=this.defaults.allowAbsoluteUrls:n.allowAbsoluteUrls=!0),xe.assertOptions(n,{baseUrl:z.spelling("baseURL"),withXsrfToken:z.spelling("withXSRFToken")},!0),n.method=(n.method||this.defaults.method||"get").toLowerCase();let o=s&&u.merge(s.common,s[n.method]);s&&u.forEach(["delete","get","head","post","put","patch","common"],h=>{delete s[h]}),n.headers=B.concat(o,s);const a=[];let c=!0;this.interceptors.request.forEach(function(g){typeof g.runWhen=="function"&&g.runWhen(n)===!1||(c=c&&g.synchronous,a.unshift(g.fulfilled,g.rejected))});const l=[];this.interceptors.response.forEach(function(g){l.push(g.fulfilled,g.rejected)});let d,f=0,p;if(!c){const h=[ln.bind(this),void 0];for(h.unshift(...a),h.push(...l),p=h.length,d=Promise.resolve(n);f<p;)d=d.then(h[f++],h[f++]);return d}p=a.length;let y=n;for(;f<p;){const h=a[f++],g=a[f++];try{y=h(y)}catch(m){g.call(this,m);break}}try{d=ln.call(this,y)}catch(h){return Promise.reject(h)}for(f=0,p=l.length;f<p;)d=d.then(l[f++],l[f++]);return d}getUri(t){t=ue(this.defaults,t);const n=nr(t.baseURL,t.url,t.allowAbsoluteUrls);return Yn(n,t.params,t.paramsSerializer)}};u.forEach(["delete","get","head","options"],function(t){ce.prototype[t]=function(n,r){return this.request(ue(r||{},{method:t,url:n,data:(r||{}).data}))}});u.forEach(["post","put","patch"],function(t){function n(r){return function(s,o,a){return this.request(ue(a||{},{method:t,headers:r?{"Content-Type":"multipart/form-data"}:{},url:s,data:o}))}}ce.prototype[t]=n(),ce.prototype[t+"Form"]=n(!0)});let Es=class ar{constructor(t){if(typeof t!="function")throw new TypeError("executor must be a function.");let n;this.promise=new Promise(function(s){n=s});const r=this;this.promise.then(i=>{if(!r._listeners)return;let s=r._listeners.length;for(;s-- >0;)r._listeners[s](i);r._listeners=null}),this.promise.then=i=>{let s;const o=new Promise(a=>{r.subscribe(a),s=a}).then(i);return o.cancel=function(){r.unsubscribe(s)},o},t(function(s,o,a){r.reason||(r.reason=new Se(s,o,a),n(r.reason))})}throwIfRequested(){if(this.reason)throw this.reason}subscribe(t){if(this.reason){t(this.reason);return}this._listeners?this._listeners.push(t):this._listeners=[t]}unsubscribe(t){if(!this._listeners)return;const n=this._listeners.indexOf(t);n!==-1&&this._listeners.splice(n,1)}toAbortSignal(){const t=new AbortController,n=r=>{t.abort(r)};return this.subscribe(n),t.signal.unsubscribe=()=>this.unsubscribe(n),t.signal}static source(){let t;return{token:new ar(function(i){t=i}),cancel:t}}};function Ss(e){return function(n){return e.apply(null,n)}}function As(e){return u.isObject(e)&&e.isAxiosError===!0}const yt={Continue:100,SwitchingProtocols:101,Processing:102,EarlyHints:103,Ok:200,Created:201,Accepted:202,NonAuthoritativeInformation:203,NoContent:204,ResetContent:205,PartialContent:206,MultiStatus:207,AlreadyReported:208,ImUsed:226,MultipleChoices:300,MovedPermanently:301,Found:302,SeeOther:303,NotModified:304,UseProxy:305,Unused:306,TemporaryRedirect:307,PermanentRedirect:308,BadRequest:400,Unauthorized:401,PaymentRequired:402,Forbidden:403,NotFound:404,MethodNotAllowed:405,NotAcceptable:406,ProxyAuthenticationRequired:407,RequestTimeout:408,Conflict:409,Gone:410,LengthRequired:411,PreconditionFailed:412,PayloadTooLarge:413,UriTooLong:414,UnsupportedMediaType:415,RangeNotSatisfiable:416,ExpectationFailed:417,ImATeapot:418,MisdirectedRequest:421,UnprocessableEntity:422,Locked:423,FailedDependency:424,TooEarly:425,UpgradeRequired:426,PreconditionRequired:428,TooManyRequests:429,RequestHeaderFieldsTooLarge:431,UnavailableForLegalReasons:451,InternalServerError:500,NotImplemented:501,BadGateway:502,ServiceUnavailable:503,GatewayTimeout:504,HttpVersionNotSupported:505,VariantAlsoNegotiates:506,InsufficientStorage:507,LoopDetected:508,NotExtended:510,NetworkAuthenticationRequired:511};Object.entries(yt).forEach(([e,t])=>{yt[t]=e});function cr(e){const t=new ce(e),n=$n(ce.prototype.request,t);return u.extend(n,ce.prototype,t,{allOwnKeys:!0}),u.extend(n,t,null,{allOwnKeys:!0}),n.create=function(i){return cr(ue(e,i))},n}const A=cr(Pe);A.Axios=ce;A.CanceledError=Se;A.CancelToken=Es;A.isCancel=er;A.VERSION=or;A.toFormData=We;A.AxiosError=w;A.Cancel=A.CanceledError;A.all=function(t){return Promise.all(t)};A.spread=Ss;A.isAxiosError=As;A.mergeConfig=ue;A.AxiosHeaders=B;A.formToJSON=e=>Zn(u.isHTMLForm(e)?new FormData(e):e);A.getAdapter=sr.getAdapter;A.HttpStatusCode=yt;A.default=A;const{Axios:Kc,AxiosError:Gc,CanceledError:Jc,isCancel:Xc,CancelToken:Yc,VERSION:Qc,all:Zc,Cancel:el,isAxiosError:tl,spread:nl,toFormData:rl,AxiosHeaders:il,HttpStatusCode:sl,formToJSON:ol,getAdapter:al,mergeConfig:cl}=A;window.axios=A;window.axios.defaults.headers.common["X-Requested-With"]="XMLHttpRequest";const Is=()=>{};var dn={};/**
 * @license
 * Copyright 2017 Google LLC
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */const lr=function(e){const t=[];let n=0;for(let r=0;r<e.length;r++){let i=e.charCodeAt(r);i<128?t[n++]=i:i<2048?(t[n++]=i>>6|192,t[n++]=i&63|128):(i&64512)===55296&&r+1<e.length&&(e.charCodeAt(r+1)&64512)===56320?(i=65536+((i&1023)<<10)+(e.charCodeAt(++r)&1023),t[n++]=i>>18|240,t[n++]=i>>12&63|128,t[n++]=i>>6&63|128,t[n++]=i&63|128):(t[n++]=i>>12|224,t[n++]=i>>6&63|128,t[n++]=i&63|128)}return t},vs=function(e){const t=[];let n=0,r=0;for(;n<e.length;){const i=e[n++];if(i<128)t[r++]=String.fromCharCode(i);else if(i>191&&i<224){const s=e[n++];t[r++]=String.fromCharCode((i&31)<<6|s&63)}else if(i>239&&i<365){const s=e[n++],o=e[n++],a=e[n++],c=((i&7)<<18|(s&63)<<12|(o&63)<<6|a&63)-65536;t[r++]=String.fromCharCode(55296+(c>>10)),t[r++]=String.fromCharCode(56320+(c&1023))}else{const s=e[n++],o=e[n++];t[r++]=String.fromCharCode((i&15)<<12|(s&63)<<6|o&63)}}return t.join("")},ur={byteToCharMap_:null,charToByteMap_:null,byteToCharMapWebSafe_:null,charToByteMapWebSafe_:null,ENCODED_VALS_BASE:"ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789",get ENCODED_VALS(){return this.ENCODED_VALS_BASE+"+/="},get ENCODED_VALS_WEBSAFE(){return this.ENCODED_VALS_BASE+"-_."},HAS_NATIVE_SUPPORT:typeof atob=="function",encodeByteArray(e,t){if(!Array.isArray(e))throw Error("encodeByteArray takes an array as a parameter");this.init_();const n=t?this.byteToCharMapWebSafe_:this.byteToCharMap_,r=[];for(let i=0;i<e.length;i+=3){const s=e[i],o=i+1<e.length,a=o?e[i+1]:0,c=i+2<e.length,l=c?e[i+2]:0,d=s>>2,f=(s&3)<<4|a>>4;let p=(a&15)<<2|l>>6,y=l&63;c||(y=64,o||(p=64)),r.push(n[d],n[f],n[p],n[y])}return r.join("")},encodeString(e,t){return this.HAS_NATIVE_SUPPORT&&!t?btoa(e):this.encodeByteArray(lr(e),t)},decodeString(e,t){return this.HAS_NATIVE_SUPPORT&&!t?atob(e):vs(this.decodeStringToByteArray(e,t))},decodeStringToByteArray(e,t){this.init_();const n=t?this.charToByteMapWebSafe_:this.charToByteMap_,r=[];for(let i=0;i<e.length;){const s=n[e.charAt(i++)],a=i<e.length?n[e.charAt(i)]:0;++i;const l=i<e.length?n[e.charAt(i)]:64;++i;const f=i<e.length?n[e.charAt(i)]:64;if(++i,s==null||a==null||l==null||f==null)throw new _s;const p=s<<2|a>>4;if(r.push(p),l!==64){const y=a<<4&240|l>>2;if(r.push(y),f!==64){const h=l<<6&192|f;r.push(h)}}}return r},init_(){if(!this.byteToCharMap_){this.byteToCharMap_={},this.charToByteMap_={},this.byteToCharMapWebSafe_={},this.charToByteMapWebSafe_={};for(let e=0;e<this.ENCODED_VALS.length;e++)this.byteToCharMap_[e]=this.ENCODED_VALS.charAt(e),this.charToByteMap_[this.byteToCharMap_[e]]=e,this.byteToCharMapWebSafe_[e]=this.ENCODED_VALS_WEBSAFE.charAt(e),this.charToByteMapWebSafe_[this.byteToCharMapWebSafe_[e]]=e,e>=this.ENCODED_VALS_BASE.length&&(this.charToByteMap_[this.ENCODED_VALS_WEBSAFE.charAt(e)]=e,this.charToByteMapWebSafe_[this.ENCODED_VALS.charAt(e)]=e)}}};class _s extends Error{constructor(){super(...arguments),this.name="DecodeBase64StringError"}}const Ts=function(e){const t=lr(e);return ur.encodeByteArray(t,!0)},dr=function(e){return Ts(e).replace(/\./g,"")},Cs=function(e){try{return ur.decodeString(e,!0)}catch(t){console.error("base64Decode failed: ",t)}return null};/**
 * @license
 * Copyright 2022 Google LLC
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */function Rs(){if(typeof self<"u")return self;if(typeof window<"u")return window;if(typeof global<"u")return global;throw new Error("Unable to locate global object.")}/**
 * @license
 * Copyright 2022 Google LLC
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */const Ds=()=>Rs().__FIREBASE_DEFAULTS__,Ps=()=>{if(typeof process>"u"||typeof dn>"u")return;const e=dn.__FIREBASE_DEFAULTS__;if(e)return JSON.parse(e)},Os=()=>{if(typeof document>"u")return;let e;try{e=document.cookie.match(/__FIREBASE_DEFAULTS__=([^;]+)/)}catch{return}const t=e&&Cs(e[1]);return t&&JSON.parse(t)},Fs=()=>{try{return Is()||Ds()||Ps()||Os()}catch(e){console.info(`Unable to get __FIREBASE_DEFAULTS__ due to: ${e}`);return}},fr=()=>{var e;return(e=Fs())==null?void 0:e.config};/**
 * @license
 * Copyright 2017 Google LLC
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */class ks{constructor(){this.reject=()=>{},this.resolve=()=>{},this.promise=new Promise((t,n)=>{this.resolve=t,this.reject=n})}wrapCallback(t){return(n,r)=>{n?this.reject(n):this.resolve(r),typeof t=="function"&&(this.promise.catch(()=>{}),t.length===1?t(n):t(n,r))}}}function hr(){const e=typeof chrome=="object"?chrome.runtime:typeof browser=="object"?browser.runtime:void 0;return typeof e=="object"&&e.id!==void 0}function Ot(){try{return typeof indexedDB=="object"}catch{return!1}}function Ft(){return new Promise((e,t)=>{try{let n=!0;const r="validate-browser-context-for-indexeddb-analytics-module",i=self.indexedDB.open(r);i.onsuccess=()=>{i.result.close(),n||self.indexedDB.deleteDatabase(r),e(!0)},i.onupgradeneeded=()=>{n=!1},i.onerror=()=>{var s;t(((s=i.error)==null?void 0:s.message)||"")}}catch(n){t(n)}})}function pr(){return!(typeof navigator>"u"||!navigator.cookieEnabled)}/**
 * @license
 * Copyright 2017 Google LLC
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */const Ls="FirebaseError";class pe extends Error{constructor(t,n,r){super(n),this.code=t,this.customData=r,this.name=Ls,Object.setPrototypeOf(this,pe.prototype),Error.captureStackTrace&&Error.captureStackTrace(this,Ke.prototype.create)}}class Ke{constructor(t,n,r){this.service=t,this.serviceName=n,this.errors=r}create(t,...n){const r=n[0]||{},i=`${this.service}/${t}`,s=this.errors[t],o=s?Bs(s,r):"Error",a=`${this.serviceName}: ${o} (${i}).`;return new pe(i,a,r)}}function Bs(e,t){try{let n=0,r="";for(;n<e.length;){const i=e.indexOf("{$",n);if(i===-1){r+=e.substring(n);break}const s=e.indexOf("}",i+2);if(s===-1){r+=e.substring(n);break}const o=e.substring(i+2,s),a=t[o];r+=e.substring(n,i)+(a!=null?String(a):`<${o}?>`),n=s+1}return r}catch{return e}}function $e(e,t){if(e===t)return!0;const n=Object.keys(e),r=Object.keys(t);for(const i of n){if(!r.includes(i))return!1;const s=e[i],o=t[i];if(fn(s)&&fn(o)){if(!$e(s,o))return!1}else if(s!==o)return!1}for(const i of r)if(!n.includes(i))return!1;return!0}function fn(e){return e!==null&&typeof e=="object"}/**
 * @license
 * Copyright 2019 Google LLC
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */const Ns=1e3,xs=2,Ms=4*60*60*1e3,$s=.5;function hn(e,t=Ns,n=xs){const r=t*Math.pow(n,e),i=Math.round($s*r*(Math.random()-.5)*2);return Math.min(Ms,r+i)}/**
 * @license
 * Copyright 2021 Google LLC
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */function kt(e){return e&&e._delegate?e._delegate:e}class ne{constructor(t,n,r){this.name=t,this.instanceFactory=n,this.type=r,this.multipleInstances=!1,this.serviceProps={},this.instantiationMode="LAZY",this.onInstanceCreated=null}setInstantiationMode(t){return this.instantiationMode=t,this}setMultipleInstances(t){return this.multipleInstances=t,this}setServiceProps(t){return this.serviceProps=t,this}setInstanceCreatedCallback(t){return this.onInstanceCreated=t,this}}/**
 * @license
 * Copyright 2019 Google LLC
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */const se="[DEFAULT]";/**
 * @license
 * Copyright 2019 Google LLC
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */class Us{constructor(t,n){this.name=t,this.container=n,this.component=null,this.instances=new Map,this.instancesDeferred=new Map,this.instancesOptions=new Map,this.onInitCallbacks=new Map}get(t){const n=this.normalizeInstanceIdentifier(t);if(!this.instancesDeferred.has(n)){const r=new ks;if(this.instancesDeferred.set(n,r),this.isInitialized(n)||this.shouldAutoInitialize())try{const i=this.getOrInitializeService({instanceIdentifier:n});i&&r.resolve(i)}catch{}}return this.instancesDeferred.get(n).promise}getImmediate(t){const n=this.normalizeInstanceIdentifier(t==null?void 0:t.identifier),r=(t==null?void 0:t.optional)??!1;if(this.isInitialized(n)||this.shouldAutoInitialize())try{return this.getOrInitializeService({instanceIdentifier:n})}catch(i){if(r)return null;throw i}else{if(r)return null;throw Error(`Service ${this.name} is not available`)}}getComponent(){return this.component}setComponent(t){if(t.name!==this.name)throw Error(`Mismatching Component ${t.name} for Provider ${this.name}.`);if(this.component)throw Error(`Component for ${this.name} has already been provided`);if(this.component=t,!!this.shouldAutoInitialize()){if(Hs(t))try{this.getOrInitializeService({instanceIdentifier:se})}catch{}for(const[n,r]of this.instancesDeferred.entries()){const i=this.normalizeInstanceIdentifier(n);try{const s=this.getOrInitializeService({instanceIdentifier:i});r.resolve(s)}catch{}}}}clearInstance(t=se){this.instancesDeferred.delete(t),this.instancesOptions.delete(t),this.instances.delete(t)}async delete(){const t=Array.from(this.instances.values());await Promise.all([...t.filter(n=>"INTERNAL"in n).map(n=>n.INTERNAL.delete()),...t.filter(n=>"_delete"in n).map(n=>n._delete())])}isComponentSet(){return this.component!=null}isInitialized(t=se){return this.instances.has(t)}getOptions(t=se){return this.instancesOptions.get(t)||{}}initialize(t={}){const{options:n={}}=t,r=this.normalizeInstanceIdentifier(t.instanceIdentifier);if(this.isInitialized(r))throw Error(`${this.name}(${r}) has already been initialized`);if(!this.isComponentSet())throw Error(`Component ${this.name} has not been registered yet`);const i=this.getOrInitializeService({instanceIdentifier:r,options:n});for(const[s,o]of this.instancesDeferred.entries()){const a=this.normalizeInstanceIdentifier(s);r===a&&o.resolve(i)}return i}onInit(t,n){const r=this.normalizeInstanceIdentifier(n),i=this.onInitCallbacks.get(r)??new Set;i.add(t),this.onInitCallbacks.set(r,i);const s=this.instances.get(r);return s&&t(s,r),()=>{i.delete(t)}}invokeOnInitCallbacks(t,n){const r=this.onInitCallbacks.get(n);if(r)for(const i of r)try{i(t,n)}catch{}}getOrInitializeService({instanceIdentifier:t,options:n={}}){let r=this.instances.get(t);if(!r&&this.component&&(r=this.component.instanceFactory(this.container,{instanceIdentifier:js(t),options:n}),this.instances.set(t,r),this.instancesOptions.set(t,n),this.invokeOnInitCallbacks(r,t),this.component.onInstanceCreated))try{this.component.onInstanceCreated(this.container,t,r)}catch{}return r||null}normalizeInstanceIdentifier(t=se){return this.component?this.component.multipleInstances?t:se:t}shouldAutoInitialize(){return!!this.component&&this.component.instantiationMode!=="EXPLICIT"}}function js(e){return e===se?void 0:e}function Hs(e){return e.instantiationMode==="EAGER"}/**
 * @license
 * Copyright 2019 Google LLC
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */class Vs{constructor(t){this.name=t,this.providers=new Map}addComponent(t){const n=this.getProvider(t.name);if(n.isComponentSet())throw new Error(`Component ${t.name} has already been registered with ${this.name}`);n.setComponent(t)}addOrOverwriteComponent(t){this.getProvider(t.name).isComponentSet()&&this.providers.delete(t.name),this.addComponent(t)}getProvider(t){if(this.providers.has(t))return this.providers.get(t);const n=new Us(t,this);return this.providers.set(t,n),n}getProviders(){return Array.from(this.providers.values())}}/**
 * @license
 * Copyright 2017 Google LLC
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */var b;(function(e){e[e.DEBUG=0]="DEBUG",e[e.VERBOSE=1]="VERBOSE",e[e.INFO=2]="INFO",e[e.WARN=3]="WARN",e[e.ERROR=4]="ERROR",e[e.SILENT=5]="SILENT"})(b||(b={}));const qs={debug:b.DEBUG,verbose:b.VERBOSE,info:b.INFO,warn:b.WARN,error:b.ERROR,silent:b.SILENT},Ws=b.INFO,zs={[b.DEBUG]:"log",[b.VERBOSE]:"log",[b.INFO]:"info",[b.WARN]:"warn",[b.ERROR]:"error"},Ks=(e,t,...n)=>{if(t<e.logLevel)return;const r=new Date().toISOString(),i=zs[t];if(i)console[i](`[${r}]  ${e.name}:`,...n);else throw new Error(`Attempted to log a message with an invalid logType (value: ${t})`)};class mr{constructor(t){this.name=t,this._logLevel=Ws,this._logHandler=Ks,this._userLogHandler=null}get logLevel(){return this._logLevel}set logLevel(t){if(!(t in b))throw new TypeError(`Invalid value "${t}" assigned to \`logLevel\``);this._logLevel=t}setLogLevel(t){this._logLevel=typeof t=="string"?qs[t]:t}get logHandler(){return this._logHandler}set logHandler(t){if(typeof t!="function")throw new TypeError("Value assigned to `logHandler` must be a function");this._logHandler=t}get userLogHandler(){return this._userLogHandler}set userLogHandler(t){this._userLogHandler=t}debug(...t){this._userLogHandler&&this._userLogHandler(this,b.DEBUG,...t),this._logHandler(this,b.DEBUG,...t)}log(...t){this._userLogHandler&&this._userLogHandler(this,b.VERBOSE,...t),this._logHandler(this,b.VERBOSE,...t)}info(...t){this._userLogHandler&&this._userLogHandler(this,b.INFO,...t),this._logHandler(this,b.INFO,...t)}warn(...t){this._userLogHandler&&this._userLogHandler(this,b.WARN,...t),this._logHandler(this,b.WARN,...t)}error(...t){this._userLogHandler&&this._userLogHandler(this,b.ERROR,...t),this._logHandler(this,b.ERROR,...t)}}const Gs=(e,t)=>t.some(n=>e instanceof n);let pn,mn;function Js(){return pn||(pn=[IDBDatabase,IDBObjectStore,IDBIndex,IDBCursor,IDBTransaction])}function Xs(){return mn||(mn=[IDBCursor.prototype.advance,IDBCursor.prototype.continue,IDBCursor.prototype.continuePrimaryKey])}const gr=new WeakMap,bt=new WeakMap,wr=new WeakMap,tt=new WeakMap,Lt=new WeakMap;function Ys(e){const t=new Promise((n,r)=>{const i=()=>{e.removeEventListener("success",s),e.removeEventListener("error",o)},s=()=>{n(Z(e.result)),i()},o=()=>{r(e.error),i()};e.addEventListener("success",s),e.addEventListener("error",o)});return t.then(n=>{n instanceof IDBCursor&&gr.set(n,e)}).catch(()=>{}),Lt.set(t,e),t}function Qs(e){if(bt.has(e))return;const t=new Promise((n,r)=>{const i=()=>{e.removeEventListener("complete",s),e.removeEventListener("error",o),e.removeEventListener("abort",o)},s=()=>{n(),i()},o=()=>{r(e.error||new DOMException("AbortError","AbortError")),i()};e.addEventListener("complete",s),e.addEventListener("error",o),e.addEventListener("abort",o)});bt.set(e,t)}let Et={get(e,t,n){if(e instanceof IDBTransaction){if(t==="done")return bt.get(e);if(t==="objectStoreNames")return e.objectStoreNames||wr.get(e);if(t==="store")return n.objectStoreNames[1]?void 0:n.objectStore(n.objectStoreNames[0])}return Z(e[t])},set(e,t,n){return e[t]=n,!0},has(e,t){return e instanceof IDBTransaction&&(t==="done"||t==="store")?!0:t in e}};function Zs(e){Et=e(Et)}function eo(e){return e===IDBDatabase.prototype.transaction&&!("objectStoreNames"in IDBTransaction.prototype)?function(t,...n){const r=e.call(nt(this),t,...n);return wr.set(r,t.sort?t.sort():[t]),Z(r)}:Xs().includes(e)?function(...t){return e.apply(nt(this),t),Z(gr.get(this))}:function(...t){return Z(e.apply(nt(this),t))}}function to(e){return typeof e=="function"?eo(e):(e instanceof IDBTransaction&&Qs(e),Gs(e,Js())?new Proxy(e,Et):e)}function Z(e){if(e instanceof IDBRequest)return Ys(e);if(tt.has(e))return tt.get(e);const t=to(e);return t!==e&&(tt.set(e,t),Lt.set(t,e)),t}const nt=e=>Lt.get(e);function no(e,t,{blocked:n,upgrade:r,blocking:i,terminated:s}={}){const o=indexedDB.open(e,t),a=Z(o);return r&&o.addEventListener("upgradeneeded",c=>{r(Z(o.result),c.oldVersion,c.newVersion,Z(o.transaction),c)}),n&&o.addEventListener("blocked",c=>n(c.oldVersion,c.newVersion,c)),a.then(c=>{s&&c.addEventListener("close",()=>s()),i&&c.addEventListener("versionchange",l=>i(l.oldVersion,l.newVersion,l))}).catch(()=>{}),a}const ro=["get","getKey","getAll","getAllKeys","count"],io=["put","add","delete","clear"],rt=new Map;function gn(e,t){if(!(e instanceof IDBDatabase&&!(t in e)&&typeof t=="string"))return;if(rt.get(t))return rt.get(t);const n=t.replace(/FromIndex$/,""),r=t!==n,i=io.includes(n);if(!(n in(r?IDBIndex:IDBObjectStore).prototype)||!(i||ro.includes(n)))return;const s=async function(o,...a){const c=this.transaction(o,i?"readwrite":"readonly");let l=c.store;return r&&(l=l.index(a.shift())),(await Promise.all([l[n](...a),i&&c.done]))[0]};return rt.set(t,s),s}Zs(e=>({...e,get:(t,n,r)=>gn(t,n)||e.get(t,n,r),has:(t,n)=>!!gn(t,n)||e.has(t,n)}));/**
 * @license
 * Copyright 2019 Google LLC
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */class so{constructor(t){this.container=t}getPlatformInfoString(){return this.container.getProviders().map(n=>{if(oo(n)){const r=n.getImmediate();return`${r.library}/${r.version}`}else return null}).filter(n=>n).join(" ")}}function oo(e){const t=e.getComponent();return(t==null?void 0:t.type)==="VERSION"}const St="@firebase/app",wn="0.16.1";/**
 * @license
 * Copyright 2019 Google LLC
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */const J=new mr("@firebase/app"),ao="@firebase/app-compat",co="@firebase/analytics-compat",lo="@firebase/analytics",uo="@firebase/app-check-compat",fo="@firebase/app-check",ho="@firebase/auth",po="@firebase/auth-compat",mo="@firebase/database",go="@firebase/data-connect",wo="@firebase/database-compat",yo="@firebase/functions",bo="@firebase/functions-compat",Eo="@firebase/installations",So="@firebase/installations-compat",Ao="@firebase/messaging",Io="@firebase/messaging-compat",vo="@firebase/performance",_o="@firebase/performance-compat",To="@firebase/remote-config",Co="@firebase/remote-config-compat",Ro="@firebase/storage",Do="@firebase/storage-compat",Po="@firebase/firestore",Oo="@firebase/ai",Fo="@firebase/firestore-compat",ko="firebase";/**
 * @license
 * Copyright 2019 Google LLC
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */const At="[DEFAULT]",Lo={[St]:"fire-core",[ao]:"fire-core-compat",[lo]:"fire-analytics",[co]:"fire-analytics-compat",[fo]:"fire-app-check",[uo]:"fire-app-check-compat",[ho]:"fire-auth",[po]:"fire-auth-compat",[mo]:"fire-rtdb",[go]:"fire-data-connect",[wo]:"fire-rtdb-compat",[yo]:"fire-fn",[bo]:"fire-fn-compat",[Eo]:"fire-iid",[So]:"fire-iid-compat",[Ao]:"fire-fcm",[Io]:"fire-fcm-compat",[vo]:"fire-perf",[_o]:"fire-perf-compat",[To]:"fire-rc",[Co]:"fire-rc-compat",[Ro]:"fire-gcs",[Do]:"fire-gcs-compat",[Po]:"fire-fst",[Fo]:"fire-fst-compat",[Oo]:"fire-vertex","fire-js":"fire-js",[ko]:"fire-js-all"};/**
 * @license
 * Copyright 2019 Google LLC
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */const _e=new Map,Bo=new Map,It=new Map;function yn(e,t){try{e.container.addComponent(t)}catch(n){J.debug(`Component ${t.name} failed to register with FirebaseApp ${e.name}`,n)}}function de(e){const t=e.name;if(It.has(t))return J.debug(`There were multiple attempts to register component ${t}.`),!1;It.set(t,e);for(const n of _e.values())yn(n,e);for(const n of Bo.values())yn(n,e);return!0}function Ge(e,t){const n=e.container.getProvider("heartbeat").getImmediate({optional:!0});return n&&n.triggerHeartbeat(),e.container.getProvider(t)}/**
 * @license
 * Copyright 2019 Google LLC
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */const No={"no-app":"No Firebase App '{$appName}' has been created - call initializeApp() first","bad-app-name":"Illegal App name: '{$appName}'","duplicate-app":"Firebase App named '{$appName}' already exists with different {$mismatchedParam}. Existing: '{$oldValue}'. New: '{$newValue}'.","app-deleted":"Firebase App named '{$appName}' already deleted","server-app-deleted":"Firebase Server App has been deleted","no-options":"Need to provide options, when not being deployed to hosting via source.","invalid-app-argument":"firebase.{$appName}() takes either no argument or a Firebase App instance.","invalid-log-argument":"First argument to `onLog` must be null or a function.","idb-open":"Error thrown when opening IndexedDB. Original error: {$originalErrorMessage}.","idb-get":"Error thrown when reading from IndexedDB. Original error: {$originalErrorMessage}.","idb-set":"Error thrown when writing to IndexedDB. Original error: {$originalErrorMessage}.","idb-delete":"Error thrown when deleting from IndexedDB. Original error: {$originalErrorMessage}.","finalization-registry-not-supported":"FirebaseServerApp deleteOnDeref field defined but the JS runtime does not support FinalizationRegistry.","invalid-server-app-environment":"FirebaseServerApp is not for use in browser environments."},G=new Ke("app","Firebase",No);/**
 * @license
 * Copyright 2019 Google LLC
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */class xo{constructor(t,n,r){this._isDeleted=!1,this._options={...t},this._config={...n},this._name=n.name,this._automaticDataCollectionEnabled=n.automaticDataCollectionEnabled,this._container=r,this.container.addComponent(new ne("app",()=>this,"PUBLIC"))}get automaticDataCollectionEnabled(){return this.checkDestroyed(),this._automaticDataCollectionEnabled}set automaticDataCollectionEnabled(t){this.checkDestroyed(),this._automaticDataCollectionEnabled=t}get name(){return this.checkDestroyed(),this._name}get options(){return this.checkDestroyed(),this._options}get config(){return this.checkDestroyed(),this._config}get container(){return this._container}get isDeleted(){return this._isDeleted}set isDeleted(t){this._isDeleted=t}checkDestroyed(){if(this.isDeleted)throw G.create("app-deleted",{appName:this._name})}}function yr(e,t={}){let n=e;typeof t!="object"&&(t={name:t});const r={name:At,automaticDataCollectionEnabled:!0,...t},i=r.name;if(typeof i!="string"||!i)throw G.create("bad-app-name",{appName:String(i)});if(n||(n=fr()),!n)throw G.create("no-options");const s=_e.get(i);if(s)if($e(n,s.options)){if($e(r,s.config))return s;throw G.create("duplicate-app",{appName:i,mismatchedParam:"config",oldValue:JSON.stringify(s.config),newValue:JSON.stringify(r)})}else throw G.create("duplicate-app",{appName:i,mismatchedParam:"options",oldValue:JSON.stringify(s.options),newValue:JSON.stringify(n)});const o=new Vs(i);for(const c of It.values())o.addComponent(c);const a=new xo(n,r,o);return _e.set(i,a),a}function br(e=At){const t=_e.get(e);if(!t&&e===At&&fr())return yr();if(!t)throw G.create("no-app",{appName:e});return t}function Mo(){return Array.from(_e.values())}function ee(e,t,n){let r=Lo[e]??e;n&&(r+=`-${n}`);const i=r.match(/\s|\//),s=t.match(/\s|\//);if(i||s){const o=[`Unable to register library "${r}" with version "${t}":`];i&&o.push(`library name "${r}" contains illegal characters (whitespace or "/")`),i&&s&&o.push("and"),s&&o.push(`version name "${t}" contains illegal characters (whitespace or "/")`),J.warn(o.join(" "));return}de(new ne(`${r}-version`,()=>({library:r,version:t}),"VERSION"))}/**
 * @license
 * Copyright 2021 Google LLC
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */const $o="firebase-heartbeat-database",Uo=1,Te="firebase-heartbeat-store";let it=null;function Er(){return it||(it=no($o,Uo,{upgrade:(e,t)=>{switch(t){case 0:try{e.createObjectStore(Te)}catch(n){console.warn(n)}}}}).catch(e=>{throw G.create("idb-open",{originalErrorMessage:e.message})})),it}async function jo(e){try{const n=(await Er()).transaction(Te),r=await n.objectStore(Te).get(Sr(e));return await n.done,r}catch(t){if(t instanceof pe)J.warn(t.message);else{const n=G.create("idb-get",{originalErrorMessage:t==null?void 0:t.message});J.warn(n.message)}}}async function bn(e,t){try{const r=(await Er()).transaction(Te,"readwrite");await r.objectStore(Te).put(t,Sr(e)),await r.done}catch(n){if(n instanceof pe)J.warn(n.message);else{const r=G.create("idb-set",{originalErrorMessage:n==null?void 0:n.message});J.warn(r.message)}}}function Sr(e){return`${e.name}!${e.options.appId}`}/**
 * @license
 * Copyright 2021 Google LLC
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */const Ho=1024,Vo=30;class qo{constructor(t){this.container=t,this._heartbeatsCache=null;const n=this.container.getProvider("app").getImmediate();this._storage=new zo(n),this._heartbeatsCachePromise=this._storage.read().then(r=>(this._heartbeatsCache=r,r))}async triggerHeartbeat(){var t,n;try{const i=this.container.getProvider("platform-logger").getImmediate().getPlatformInfoString(),s=En();if(((t=this._heartbeatsCache)==null?void 0:t.heartbeats)==null&&(this._heartbeatsCache=await this._heartbeatsCachePromise,((n=this._heartbeatsCache)==null?void 0:n.heartbeats)==null)||this._heartbeatsCache.lastSentHeartbeatDate===s||this._heartbeatsCache.heartbeats.some(o=>o.date===s))return;if(this._heartbeatsCache.heartbeats.push({date:s,agent:i}),this._heartbeatsCache.heartbeats.length>Vo){const o=Ko(this._heartbeatsCache.heartbeats);this._heartbeatsCache.heartbeats.splice(o,1)}return this._storage.overwrite(this._heartbeatsCache)}catch(r){J.warn(r)}}async getHeartbeatsHeader(){var t;try{if(this._heartbeatsCache===null&&await this._heartbeatsCachePromise,((t=this._heartbeatsCache)==null?void 0:t.heartbeats)==null||this._heartbeatsCache.heartbeats.length===0)return"";const n=En(),{heartbeatsToSend:r,unsentEntries:i}=Wo(this._heartbeatsCache.heartbeats),s=dr(JSON.stringify({version:2,heartbeats:r}));return this._heartbeatsCache.lastSentHeartbeatDate=n,i.length>0?(this._heartbeatsCache.heartbeats=i,await this._storage.overwrite(this._heartbeatsCache)):(this._heartbeatsCache.heartbeats=[],this._storage.overwrite(this._heartbeatsCache)),s}catch(n){return J.warn(n),""}}}function En(){return new Date().toISOString().substring(0,10)}function Wo(e,t=Ho){const n=[];let r=e.slice();for(const i of e){const s=n.find(o=>o.agent===i.agent);if(s){if(s.dates.push(i.date),Sn(n)>t){s.dates.pop();break}}else if(n.push({agent:i.agent,dates:[i.date]}),Sn(n)>t){n.pop();break}r=r.slice(1)}return{heartbeatsToSend:n,unsentEntries:r}}class zo{constructor(t){this.app=t,this._canUseIndexedDBPromise=this.runIndexedDBEnvironmentCheck()}async runIndexedDBEnvironmentCheck(){return Ot()?Ft().then(()=>!0).catch(()=>!1):!1}async read(){if(await this._canUseIndexedDBPromise){const n=await jo(this.app);return n!=null&&n.heartbeats?n:{heartbeats:[]}}else return{heartbeats:[]}}async overwrite(t){if(await this._canUseIndexedDBPromise){const r=await this.read();return bn(this.app,{lastSentHeartbeatDate:t.lastSentHeartbeatDate??r.lastSentHeartbeatDate,heartbeats:t.heartbeats})}else return}async add(t){if(await this._canUseIndexedDBPromise){const r=await this.read();return bn(this.app,{lastSentHeartbeatDate:t.lastSentHeartbeatDate??r.lastSentHeartbeatDate,heartbeats:[...r.heartbeats,...t.heartbeats]})}else return}}function Sn(e){return dr(JSON.stringify({version:2,heartbeats:e})).length}function Ko(e){if(e.length===0)return-1;let t=0,n=e[0].date;for(let r=1;r<e.length;r++)e[r].date<n&&(n=e[r].date,t=r);return t}/**
 * @license
 * Copyright 2019 Google LLC
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */function Go(e){de(new ne("platform-logger",t=>new so(t),"PRIVATE")),de(new ne("heartbeat",t=>new qo(t),"PRIVATE")),ee(St,wn,e),ee(St,wn,"esm2020"),ee("fire-js","")}/**
 * @license
 * Copyright 2019 Google LLC
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */Go("");var Jo="firebase",Xo="12.18.0";/**
 * @license
 * Copyright 2020 Google LLC
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */ee(Jo,Xo,"app");const Yo=(e,t)=>t.some(n=>e instanceof n);let An,In;function Qo(){return An||(An=[IDBDatabase,IDBObjectStore,IDBIndex,IDBCursor,IDBTransaction])}function Zo(){return In||(In=[IDBCursor.prototype.advance,IDBCursor.prototype.continue,IDBCursor.prototype.continuePrimaryKey])}const Ar=new WeakMap,vt=new WeakMap,Ir=new WeakMap,st=new WeakMap,Bt=new WeakMap;function ea(e){const t=new Promise((n,r)=>{const i=()=>{e.removeEventListener("success",s),e.removeEventListener("error",o)},s=()=>{n(te(e.result)),i()},o=()=>{r(e.error),i()};e.addEventListener("success",s),e.addEventListener("error",o)});return t.then(n=>{n instanceof IDBCursor&&Ar.set(n,e)}).catch(()=>{}),Bt.set(t,e),t}function ta(e){if(vt.has(e))return;const t=new Promise((n,r)=>{const i=()=>{e.removeEventListener("complete",s),e.removeEventListener("error",o),e.removeEventListener("abort",o)},s=()=>{n(),i()},o=()=>{r(e.error||new DOMException("AbortError","AbortError")),i()};e.addEventListener("complete",s),e.addEventListener("error",o),e.addEventListener("abort",o)});vt.set(e,t)}let _t={get(e,t,n){if(e instanceof IDBTransaction){if(t==="done")return vt.get(e);if(t==="objectStoreNames")return e.objectStoreNames||Ir.get(e);if(t==="store")return n.objectStoreNames[1]?void 0:n.objectStore(n.objectStoreNames[0])}return te(e[t])},set(e,t,n){return e[t]=n,!0},has(e,t){return e instanceof IDBTransaction&&(t==="done"||t==="store")?!0:t in e}};function na(e){_t=e(_t)}function ra(e){return e===IDBDatabase.prototype.transaction&&!("objectStoreNames"in IDBTransaction.prototype)?function(t,...n){const r=e.call(ot(this),t,...n);return Ir.set(r,t.sort?t.sort():[t]),te(r)}:Zo().includes(e)?function(...t){return e.apply(ot(this),t),te(Ar.get(this))}:function(...t){return te(e.apply(ot(this),t))}}function ia(e){return typeof e=="function"?ra(e):(e instanceof IDBTransaction&&ta(e),Yo(e,Qo())?new Proxy(e,_t):e)}function te(e){if(e instanceof IDBRequest)return ea(e);if(st.has(e))return st.get(e);const t=ia(e);return t!==e&&(st.set(e,t),Bt.set(t,e)),t}const ot=e=>Bt.get(e);function sa(e,t,{blocked:n,upgrade:r,blocking:i,terminated:s}={}){const o=indexedDB.open(e,t),a=te(o);return r&&o.addEventListener("upgradeneeded",c=>{r(te(o.result),c.oldVersion,c.newVersion,te(o.transaction),c)}),n&&o.addEventListener("blocked",c=>n(c.oldVersion,c.newVersion,c)),a.then(c=>{s&&c.addEventListener("close",()=>s()),i&&c.addEventListener("versionchange",l=>i(l.oldVersion,l.newVersion,l))}).catch(()=>{}),a}const oa=["get","getKey","getAll","getAllKeys","count"],aa=["put","add","delete","clear"],at=new Map;function vn(e,t){if(!(e instanceof IDBDatabase&&!(t in e)&&typeof t=="string"))return;if(at.get(t))return at.get(t);const n=t.replace(/FromIndex$/,""),r=t!==n,i=aa.includes(n);if(!(n in(r?IDBIndex:IDBObjectStore).prototype)||!(i||oa.includes(n)))return;const s=async function(o,...a){const c=this.transaction(o,i?"readwrite":"readonly");let l=c.store;return r&&(l=l.index(a.shift())),(await Promise.all([l[n](...a),i&&c.done]))[0]};return at.set(t,s),s}na(e=>({...e,get:(t,n,r)=>vn(t,n)||e.get(t,n,r),has:(t,n)=>!!vn(t,n)||e.has(t,n)}));const vr="@firebase/installations",Nt="0.6.24";/**
 * @license
 * Copyright 2019 Google LLC
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */const _r=1e4,Tr=`w:${Nt}`,Cr="FIS_v2",ca="https://firebaseinstallations.googleapis.com/v1",la=60*60*1e3,ua="installations",da="Installations";/**
 * @license
 * Copyright 2019 Google LLC
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */const fa={"missing-app-config-values":'Missing App configuration value: "{$valueName}"',"not-registered":"Firebase Installation is not registered.","installation-not-found":"Firebase Installation not found.","request-failed":'{$requestName} request failed with error "{$serverCode} {$serverStatus}: {$serverMessage}"',"app-offline":"Could not process request. Application offline.","delete-pending-registration":"Can't delete installation while there is a pending registration request."},fe=new Ke(ua,da,fa);function Rr(e){return e instanceof pe&&e.code.includes("request-failed")}/**
 * @license
 * Copyright 2019 Google LLC
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */function Dr({projectId:e}){return`${ca}/projects/${e}/installations`}function Pr(e){return{token:e.token,requestStatus:2,expiresIn:pa(e.expiresIn),creationTime:Date.now()}}async function Or(e,t){const r=(await t.json()).error;return fe.create("request-failed",{requestName:e,serverCode:r.code,serverMessage:r.message,serverStatus:r.status})}function Fr({apiKey:e}){return new Headers({"Content-Type":"application/json",Accept:"application/json","x-goog-api-key":e})}function ha(e,{refreshToken:t}){const n=Fr(e);return n.append("Authorization",ma(t)),n}async function kr(e){const t=await e();return t.status>=500&&t.status<600?e():t}function pa(e){return Number(e.replace("s","000"))}function ma(e){return`${Cr} ${e}`}/**
 * @license
 * Copyright 2019 Google LLC
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */async function ga({appConfig:e,heartbeatServiceProvider:t},{fid:n}){const r=Dr(e),i=Fr(e),s=t.getImmediate({optional:!0});if(s){const l=await s.getHeartbeatsHeader();l&&i.append("x-firebase-client",l)}const o={fid:n,authVersion:Cr,appId:e.appId,sdkVersion:Tr},a={method:"POST",headers:i,body:JSON.stringify(o)},c=await kr(()=>fetch(r,a));if(c.ok){const l=await c.json();return{fid:l.fid||n,registrationStatus:2,refreshToken:l.refreshToken,authToken:Pr(l.authToken)}}else throw await Or("Create Installation",c)}/**
 * @license
 * Copyright 2019 Google LLC
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */function Lr(e){return new Promise(t=>{setTimeout(t,e)})}/**
 * @license
 * Copyright 2019 Google LLC
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */function wa(e){return btoa(String.fromCharCode(...e)).replace(/\+/g,"-").replace(/\//g,"_")}/**
 * @license
 * Copyright 2019 Google LLC
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */const ya=/^[cdef][\w-]{21}$/,Tt="";function ba(){try{const e=new Uint8Array(17);(self.crypto||self.msCrypto).getRandomValues(e),e[0]=112+e[0]%16;const n=Ea(e);return ya.test(n)?n:Tt}catch{return Tt}}function Ea(e){return wa(e).substr(0,22)}/**
 * @license
 * Copyright 2019 Google LLC
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */function Je(e){return`${e.appName}!${e.appId}`}/**
 * @license
 * Copyright 2019 Google LLC
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */const Br=new Map;function Nr(e,t){const n=Je(e);xr(n,t),Sa(n,t)}function xr(e,t){const n=Br.get(e);if(n)for(const r of n)r(t)}function Sa(e,t){const n=Aa();n&&n.postMessage({key:e,fid:t}),Ia()}let ae=null;function Aa(){return!ae&&"BroadcastChannel"in self&&(ae=new BroadcastChannel("[Firebase] FID Change"),ae.onmessage=e=>{xr(e.data.key,e.data.fid)}),ae}function Ia(){Br.size===0&&ae&&(ae.close(),ae=null)}/**
 * @license
 * Copyright 2019 Google LLC
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */const va="firebase-installations-database",_a=1,he="firebase-installations-store";let ct=null;function xt(){return ct||(ct=sa(va,_a,{upgrade:(e,t)=>{switch(t){case 0:e.createObjectStore(he)}}})),ct}async function Ue(e,t){const n=Je(e),i=(await xt()).transaction(he,"readwrite"),s=i.objectStore(he),o=await s.get(n);return await s.put(t,n),await i.done,(!o||o.fid!==t.fid)&&Nr(e,t.fid),t}async function Mr(e){const t=Je(e),r=(await xt()).transaction(he,"readwrite");await r.objectStore(he).delete(t),await r.done}async function Xe(e,t){const n=Je(e),i=(await xt()).transaction(he,"readwrite"),s=i.objectStore(he),o=await s.get(n),a=t(o);return a===void 0?await s.delete(n):await s.put(a,n),await i.done,a&&(!o||o.fid!==a.fid)&&Nr(e,a.fid),a}/**
 * @license
 * Copyright 2019 Google LLC
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */async function Mt(e){let t;const n=await Xe(e.appConfig,r=>{const i=Ta(r),s=Ca(e,i);return t=s.registrationPromise,s.installationEntry});return n.fid===Tt?{installationEntry:await t}:{installationEntry:n,registrationPromise:t}}function Ta(e){const t=e||{fid:ba(),registrationStatus:0};return $r(t)}function Ca(e,t){if(t.registrationStatus===0){if(!navigator.onLine){const i=Promise.reject(fe.create("app-offline"));return{installationEntry:t,registrationPromise:i}}const n={fid:t.fid,registrationStatus:1,registrationTime:Date.now()},r=Ra(e,n);return{installationEntry:n,registrationPromise:r}}else return t.registrationStatus===1?{installationEntry:t,registrationPromise:Da(e)}:{installationEntry:t}}async function Ra(e,t){try{const n=await ga(e,t);return Ue(e.appConfig,n)}catch(n){throw Rr(n)&&n.customData.serverCode===409?await Mr(e.appConfig):await Ue(e.appConfig,{fid:t.fid,registrationStatus:0}),n}}async function Da(e){let t=await _n(e.appConfig);for(;t.registrationStatus===1;)await Lr(100),t=await _n(e.appConfig);if(t.registrationStatus===0){const{installationEntry:n,registrationPromise:r}=await Mt(e);return r||n}return t}function _n(e){return Xe(e,t=>{if(!t)throw fe.create("installation-not-found");return $r(t)})}function $r(e){return Pa(e)?{fid:e.fid,registrationStatus:0}:e}function Pa(e){return e.registrationStatus===1&&e.registrationTime+_r<Date.now()}/**
 * @license
 * Copyright 2019 Google LLC
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */async function Oa({appConfig:e,heartbeatServiceProvider:t},n){const r=Fa(e,n),i=ha(e,n),s=t.getImmediate({optional:!0});if(s){const l=await s.getHeartbeatsHeader();l&&i.append("x-firebase-client",l)}const o={installation:{sdkVersion:Tr,appId:e.appId}},a={method:"POST",headers:i,body:JSON.stringify(o)},c=await kr(()=>fetch(r,a));if(c.ok){const l=await c.json();return Pr(l)}else throw await Or("Generate Auth Token",c)}function Fa(e,{fid:t}){return`${Dr(e)}/${t}/authTokens:generate`}/**
 * @license
 * Copyright 2019 Google LLC
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */async function $t(e,t=!1){let n;const r=await Xe(e.appConfig,s=>{if(!Ur(s))throw fe.create("not-registered");const o=s.authToken;if(!t&&Ba(o))return s;if(o.requestStatus===1)return n=ka(e,t),s;{if(!navigator.onLine)throw fe.create("app-offline");const a=xa(s);return n=La(e,a),a}});return n?await n:r.authToken}async function ka(e,t){let n=await Tn(e.appConfig);for(;n.authToken.requestStatus===1;)await Lr(100),n=await Tn(e.appConfig);const r=n.authToken;return r.requestStatus===0?$t(e,t):r}function Tn(e){return Xe(e,t=>{if(!Ur(t))throw fe.create("not-registered");const n=t.authToken;return Ma(n)?{...t,authToken:{requestStatus:0}}:t})}async function La(e,t){try{const n=await Oa(e,t),r={...t,authToken:n};return await Ue(e.appConfig,r),n}catch(n){if(Rr(n)&&(n.customData.serverCode===401||n.customData.serverCode===404))await Mr(e.appConfig);else{const r={...t,authToken:{requestStatus:0}};await Ue(e.appConfig,r)}throw n}}function Ur(e){return e!==void 0&&e.registrationStatus===2}function Ba(e){return e.requestStatus===2&&!Na(e)}function Na(e){const t=Date.now();return t<e.creationTime||e.creationTime+e.expiresIn<t+la}function xa(e){const t={requestStatus:1,requestTime:Date.now()};return{...e,authToken:t}}function Ma(e){return e.requestStatus===1&&e.requestTime+_r<Date.now()}/**
 * @license
 * Copyright 2019 Google LLC
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */async function $a(e){const t=e,{installationEntry:n,registrationPromise:r}=await Mt(t);return r?r.catch(console.error):$t(t).catch(console.error),n.fid}/**
 * @license
 * Copyright 2019 Google LLC
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */async function Ua(e,t=!1){const n=e;return await ja(n),(await $t(n,t)).token}async function ja(e){const{registrationPromise:t}=await Mt(e);t&&await t}/**
 * @license
 * Copyright 2019 Google LLC
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */function Ha(e){if(!e||!e.options)throw lt("App Configuration");if(!e.name)throw lt("App Name");const t=["projectId","apiKey","appId"];for(const n of t)if(!e.options[n])throw lt(n);return{appName:e.name,projectId:e.options.projectId,apiKey:e.options.apiKey,appId:e.options.appId}}function lt(e){return fe.create("missing-app-config-values",{valueName:e})}/**
 * @license
 * Copyright 2020 Google LLC
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */const jr="installations",Va="installations-internal",qa=e=>{const t=e.getProvider("app").getImmediate(),n=Ha(t),r=Ge(t,"heartbeat");return{app:t,appConfig:n,heartbeatServiceProvider:r,_delete:()=>Promise.resolve()}},Wa=e=>{const t=e.getProvider("app").getImmediate(),n=Ge(t,jr).getImmediate();return{getId:()=>$a(n),getToken:i=>Ua(n,i)}};function za(){de(new ne(jr,qa,"PUBLIC")),de(new ne(Va,Wa,"PRIVATE"))}/**
 * @license
 * Copyright 2019 Google LLC
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */za();ee(vr,Nt);ee(vr,Nt,"esm2020");/**
 * @license
 * Copyright 2019 Google LLC
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */const je="analytics",Ka="firebase_id",Ga="origin",Ja=60*1e3,Xa="https://firebase.googleapis.com/v1alpha/projects/-/apps/{app-id}/webConfig",Ut="https://www.googletagmanager.com/gtag/js";/**
 * @license
 * Copyright 2019 Google LLC
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */const O=new mr("@firebase/analytics");/**
 * @license
 * Copyright 2019 Google LLC
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */const Ya={"already-exists":"A Firebase Analytics instance with the appId {$id}  already exists. Only one Firebase Analytics instance can be created for each appId.","already-initialized":"initializeAnalytics() cannot be called again with different options than those it was initially called with. It can be called again with the same options to return the existing instance, or getAnalytics() can be used to get a reference to the already-initialized instance.","already-initialized-settings":"Firebase Analytics has already been initialized.settings() must be called before initializing any Analytics instanceor it will have no effect.","interop-component-reg-failed":"Firebase Analytics Interop Component failed to instantiate: {$reason}","invalid-analytics-context":"Firebase Analytics is not supported in this environment. Wrap initialization of analytics in analytics.isSupported() to prevent initialization in unsupported environments. Details: {$errorInfo}","indexeddb-unavailable":"IndexedDB unavailable or restricted in this environment. Wrap initialization of analytics in analytics.isSupported() to prevent initialization in unsupported environments. Details: {$errorInfo}","fetch-throttle":"The config fetch request timed out while in an exponential backoff state. Unix timestamp in milliseconds when fetch request throttling ends: {$throttleEndTimeMillis}.","config-fetch-failed":"Dynamic config fetch failed: [{$httpStatus}] {$responseMessage}","no-api-key":'The "apiKey" field is empty in the local Firebase config. Firebase Analytics requires this field tocontain a valid API key.',"no-app-id":'The "appId" field is empty in the local Firebase config. Firebase Analytics requires this field tocontain a valid app ID.',"no-client-id":'The "client_id" field is empty.',"invalid-gtag-resource":"Trusted Types detected an invalid gtag resource: {$gtagURL}."},$=new Ke("analytics","Analytics",Ya);/**
 * @license
 * Copyright 2019 Google LLC
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */function Qa(e){if(!e.startsWith(Ut)){const t=$.create("invalid-gtag-resource",{gtagURL:e});return O.warn(t.message),""}return e}function Hr(e){return Promise.all(e.map(t=>t.catch(n=>n)))}function Za(e,t){let n;return window.trustedTypes&&(n=window.trustedTypes.createPolicy(e,t)),n}function ec(e,t){const n=Za("firebase-js-sdk-policy",{createScriptURL:Qa}),r=document.createElement("script"),i=`${Ut}?l=${e}&id=${t}`;r.src=n?n==null?void 0:n.createScriptURL(i):i,r.async=!0,document.head.appendChild(r)}function tc(e){let t=[];return Array.isArray(window[e])?t=window[e]:window[e]=t,t}async function nc(e,t,n,r,i,s){const o=r[i];try{if(o)await t[o];else{const c=(await Hr(n)).find(l=>l.measurementId===i);c&&await t[c.appId]}}catch(a){O.error(a)}e("config",i,s)}async function rc(e,t,n,r,i){try{let s=[];if(i&&i.send_to){let o=i.send_to;Array.isArray(o)||(o=[o]);const a=await Hr(n);for(const c of o){const l=a.find(f=>f.measurementId===c),d=l&&t[l.appId];if(d)s.push(d);else{s=[];break}}}s.length===0&&(s=Object.values(t)),await Promise.all(s),e("event",r,i||{})}catch(s){O.error(s)}}function ic(e,t,n,r){async function i(s,...o){try{if(s==="event"){const[a,c]=o;await rc(e,t,n,a,c)}else if(s==="config"){const[a,c]=o;await nc(e,t,n,r,a,c)}else if(s==="consent"){const[a,c]=o;e("consent",a,c)}else if(s==="get"){const[a,c,l]=o;e("get",a,c,l)}else if(s==="set"){const[a]=o;e("set",a)}else e(s,...o)}catch(a){O.error(a)}}return i}function sc(e,t,n,r,i){let s=function(...o){window[r].push(arguments)};return window[i]&&typeof window[i]=="function"&&(s=window[i]),window[i]=ic(s,e,t,n),{gtagCore:s,wrappedGtag:window[i]}}function oc(e){const t=window.document.getElementsByTagName("script");for(const n of Object.values(t))if(n.src&&n.src.includes(Ut)&&n.src.includes(e))return n;return null}/**
 * @license
 * Copyright 2020 Google LLC
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */const ac=30,cc=1e3;class lc{constructor(t={},n=cc){this.throttleMetadata=t,this.intervalMillis=n}getThrottleMetadata(t){return this.throttleMetadata[t]}setThrottleMetadata(t,n){this.throttleMetadata[t]=n}deleteThrottleMetadata(t){delete this.throttleMetadata[t]}}const Vr=new lc;function uc(e){return new Headers({Accept:"application/json","x-goog-api-key":e})}async function dc(e){var o;const{appId:t,apiKey:n}=e,r={method:"GET",headers:uc(n)},i=Xa.replace("{app-id}",t),s=await fetch(i,r);if(s.status!==200&&s.status!==304){let a="";try{const c=await s.json();(o=c.error)!=null&&o.message&&(a=c.error.message)}catch{}throw $.create("config-fetch-failed",{httpStatus:s.status,responseMessage:a})}return s.json()}async function fc(e,t=Vr,n){const{appId:r,apiKey:i,measurementId:s}=e.options;if(!r)throw $.create("no-app-id");if(!i){if(s)return{measurementId:s,appId:r};throw $.create("no-api-key")}const o=t.getThrottleMetadata(r)||{backoffCount:0,throttleEndTimeMillis:Date.now()},a=new mc;return setTimeout(async()=>{a.abort()},Ja),qr({appId:r,apiKey:i,measurementId:s},o,a,t)}async function qr(e,{throttleEndTimeMillis:t,backoffCount:n},r,i=Vr){var a;const{appId:s,measurementId:o}=e;try{await hc(r,t)}catch(c){if(o)return O.warn(`Timed out fetching this Firebase app's measurement ID from the server. Falling back to the measurement ID ${o} provided in the "measurementId" field in the local Firebase config. [${c==null?void 0:c.message}]`),{appId:s,measurementId:o};throw c}try{const c=await dc(e);return i.deleteThrottleMetadata(s),c}catch(c){const l=c;if(!pc(l)){if(i.deleteThrottleMetadata(s),o)return O.warn(`Failed to fetch this Firebase app's measurement ID from the server. Falling back to the measurement ID ${o} provided in the "measurementId" field in the local Firebase config. [${l==null?void 0:l.message}]`),{appId:s,measurementId:o};throw c}const d=Number((a=l==null?void 0:l.customData)==null?void 0:a.httpStatus)===503?hn(n,i.intervalMillis,ac):hn(n,i.intervalMillis),f={throttleEndTimeMillis:Date.now()+d,backoffCount:n+1};return i.setThrottleMetadata(s,f),O.debug(`Calling attemptFetch again in ${d} millis`),qr(e,f,r,i)}}function hc(e,t){return new Promise((n,r)=>{const i=Math.max(t-Date.now(),0),s=setTimeout(n,i);e.addEventListener(()=>{clearTimeout(s),r($.create("fetch-throttle",{throttleEndTimeMillis:t}))})})}function pc(e){if(!(e instanceof pe)||!e.customData)return!1;const t=Number(e.customData.httpStatus);return t===429||t===500||t===503||t===504}class mc{constructor(){this.listeners=[]}addEventListener(t){this.listeners.push(t)}abort(){this.listeners.forEach(t=>t())}}async function gc(e,t,n,r,i){if(i&&i.global){e("event",n,r);return}else{const s=await t,o={...r,send_to:s};e("event",n,o)}}async function wc(e,t,n,r){if(r&&r.global){const i={};for(const s of Object.keys(n))i[`user_properties.${s}`]=n[s];return e("set",i),Promise.resolve()}else{const i=await t;e("config",i,{update:!0,user_properties:n})}}/**
 * @license
 * Copyright 2020 Google LLC
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */async function yc(){if(Ot())try{await Ft()}catch(e){return O.warn($.create("indexeddb-unavailable",{errorInfo:e==null?void 0:e.toString()}).message),!1}else return O.warn($.create("indexeddb-unavailable",{errorInfo:"IndexedDB is not available in this environment."}).message),!1;return!0}async function bc(e,t,n,r,i,s,o){const a=fc(e);a.then(p=>{n[p.measurementId]=p.appId,e.options.measurementId&&p.measurementId!==e.options.measurementId&&O.warn(`The measurement ID in the local Firebase config (${e.options.measurementId}) does not match the measurement ID fetched from the server (${p.measurementId}). To ensure analytics events are always sent to the correct Analytics property, update the measurement ID field in the local config or remove it from the local config.`)}).catch(p=>O.error(p)),t.push(a);const c=yc().then(p=>{if(p)return r.getId()}),[l,d]=await Promise.all([a,c]);oc(s)||ec(s,l.measurementId),i("js",new Date);const f=(o==null?void 0:o.config)??{};return f[Ga]="firebase",f.update=!0,d!=null&&(f[Ka]=d),i("config",l.measurementId,f),l.measurementId}/**
 * @license
 * Copyright 2019 Google LLC
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */class Ec{constructor(t){this.app=t}_delete(){return delete ge[this.app.options.appId],Promise.resolve()}}let ge={},Cn=[];const Rn={};let ut="dataLayer",Sc="gtag",Dn,jt,Pn=!1;function Ac(){const e=[];if(hr()&&e.push("This is a browser extension environment."),pr()||e.push("Cookies are not available."),e.length>0){const t=e.map((r,i)=>`(${i+1}) ${r}`).join(" "),n=$.create("invalid-analytics-context",{errorInfo:t});O.warn(n.message)}}function Ic(e,t,n){Ac();const r=e.options.appId;if(!r)throw $.create("no-app-id");if(!e.options.apiKey)if(e.options.measurementId)O.warn(`The "apiKey" field is empty in the local Firebase config. This is needed to fetch the latest measurement ID for this Firebase app. Falling back to the measurement ID ${e.options.measurementId} provided in the "measurementId" field in the local Firebase config.`);else throw $.create("no-api-key");if(ge[r]!=null)throw $.create("already-exists",{id:r});if(!Pn){tc(ut);const{wrappedGtag:s,gtagCore:o}=sc(ge,Cn,Rn,ut,Sc);jt=s,Dn=o,Pn=!0}return ge[r]=bc(e,Cn,Rn,t,Dn,ut,n),new Ec(e)}/**
 * @license
 * Copyright 2020 Google LLC
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */function vc(e=br()){e=kt(e);const t=Ge(e,je);return t.isInitialized()?t.getImmediate():_c(e)}function _c(e,t={}){const n=Ge(e,je);if(n.isInitialized()){const i=n.getImmediate();if($e(t,n.getOptions()))return i;throw $.create("already-initialized")}return n.initialize({options:t})}async function Tc(){if(hr()||!pr()||!Ot())return!1;try{return await Ft()}catch{return!1}}function Cc(e,t,n){e=kt(e),wc(jt,ge[e.app.options.appId],t,n).catch(r=>O.error(r))}function Rc(e,t,n,r){e=kt(e),gc(jt,ge[e.app.options.appId],t,n,r).catch(i=>O.error(i))}const On="@firebase/analytics",Fn="0.10.24";/**
 * @license
 * Copyright 2019 Google LLC
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */function Dc(){de(new ne(je,(t,{options:n})=>{const r=t.getProvider("app").getImmediate(),i=t.getProvider("installations-internal").getImmediate();return Ic(r,i,n)},"PUBLIC")),de(new ne("analytics-internal",e,"PRIVATE")),ee(On,Fn),ee(On,Fn,"esm2020");function e(t){try{const n=t.getProvider(je).getImmediate();return{logEvent:(r,i,s)=>Rc(n,r,i,s),setUserProperties:(r,i)=>Cc(n,r,i)}}catch(n){throw $.create("interop-component-reg-failed",{reason:n})}}}Dc();function Pc(){return{apiKey:"AIzaSyAWwIBl3sKDKoMO97_mw_L9rLpDajqQgsY",authDomain:"filefusion-f7889.firebaseapp.com",projectId:"filefusion-f7889",storageBucket:"filefusion-f7889.firebasestorage.app",messagingSenderId:"932828976377",appId:"1:932828976377:web:d0a6a3c5a16c95672d7e28",measurementId:"G-QRJ6ZQ8WD8"}}let dt=null,Oc=null;try{const e=Pc();e.apiKey&&(dt=Mo().length===0?yr(e):br(),typeof window<"u"&&Tc().then(t=>{t&&dt&&(Oc=vc(dt),console.log("[Firebase SDK] Initialized with Project:",e.projectId))}).catch(()=>{}))}catch(e){console.warn("[Firebase SDK Init]:",e)}/*! Capacitor: https://capacitorjs.com/ - MIT License */var ye;(function(e){e.Unimplemented="UNIMPLEMENTED",e.Unavailable="UNAVAILABLE"})(ye||(ye={}));class ft extends Error{constructor(t,n,r){super(t),this.message=t,this.code=n,this.data=r}}const Fc=e=>{var t,n;return e!=null&&e.androidBridge?"android":!((n=(t=e==null?void 0:e.webkit)===null||t===void 0?void 0:t.messageHandlers)===null||n===void 0)&&n.bridge?"ios":"web"},kc=e=>{const t=e.CapacitorCustomPlatform||null,n=e.Capacitor||{},r=n.Plugins=n.Plugins||{},i=()=>t!==null?t.name:Fc(e),s=()=>i()!=="web",o=f=>{const p=l.get(f);return!!(p!=null&&p.platforms.has(i())||a(f))},a=f=>{var p;return(p=n.PluginHeaders)===null||p===void 0?void 0:p.find(y=>y.name===f)},c=f=>e.console.error(f),l=new Map,d=(f,p={})=>{const y=l.get(f);if(y)return console.warn(`Capacitor plugin "${f}" already registered. Cannot register plugins twice.`),y.proxy;const h=i(),g=a(f);let m;const S=async()=>(!m&&h in p?m=typeof p[h]=="function"?m=await p[h]():m=p[h]:t!==null&&!m&&"web"in p&&(m=typeof p.web=="function"?m=await p.web():m=p.web),m),F=(I,_)=>{var N,V;if(g){const x=g==null?void 0:g.methods.find(C=>_===C.name);if(x)return x.rtype==="promise"?C=>n.nativePromise(f,_.toString(),C):(C,U)=>n.nativeCallback(f,_.toString(),C,U);if(I)return(N=I[_])===null||N===void 0?void 0:N.bind(I)}else{if(I)return(V=I[_])===null||V===void 0?void 0:V.bind(I);throw new ft(`"${f}" plugin is not implemented on ${h}`,ye.Unimplemented)}},E=I=>{let _;const N=(...V)=>{const x=S().then(C=>{const U=F(C,I);if(U){const re=U(...V);return _=re==null?void 0:re.remove,re}else throw new ft(`"${f}.${I}()" is not implemented on ${h}`,ye.Unimplemented)});return I==="addListener"&&(x.remove=async()=>_()),x};return N.toString=()=>`${I.toString()}() { [capacitor code] }`,Object.defineProperty(N,"name",{value:I,writable:!1,configurable:!1}),N},T=E("addListener"),k=E("removeListener"),K=(I,_)=>{const N=T({eventName:I},_),V=async()=>{const C=await N;k({eventName:I,callbackId:C},_)},x=new Promise(C=>N.then(()=>C({remove:V})));return x.remove=async()=>{console.warn("Using addListener() without 'await' is deprecated."),await V()},x},R=new Proxy({},{get(I,_){switch(_){case"$$typeof":return;case"toJSON":return()=>({});case"addListener":return g?K:T;case"removeListener":return k;default:return E(_)}}});return r[f]=R,l.set(f,{name:f,proxy:R,platforms:new Set([...Object.keys(p),...g?[h]:[]])}),R};return n.convertFileSrc||(n.convertFileSrc=f=>f),n.getPlatform=i,n.handleError=c,n.isNativePlatform=s,n.isPluginAvailable=o,n.registerPlugin=d,n.Exception=ft,n.DEBUG=!!n.DEBUG,n.isLoggingEnabled=!!n.isLoggingEnabled,n},Lc=e=>e.Capacitor=kc(e),le=Lc(typeof globalThis<"u"?globalThis:typeof self<"u"?self:typeof window<"u"?window:typeof global<"u"?global:{}),X=le.registerPlugin;class Ht{constructor(){this.listeners={},this.retainedEventArguments={},this.windowListeners={}}addListener(t,n){let r=!1;this.listeners[t]||(this.listeners[t]=[],r=!0),this.listeners[t].push(n);const s=this.windowListeners[t];s&&!s.registered&&this.addWindowListener(s),r&&this.sendRetainedArgumentsForEvent(t);const o=async()=>this.removeListener(t,n);return Promise.resolve({remove:o})}async removeAllListeners(){this.listeners={};for(const t in this.windowListeners)this.removeWindowListener(this.windowListeners[t]);this.windowListeners={}}notifyListeners(t,n,r){const i=this.listeners[t];if(!i){if(r){let s=this.retainedEventArguments[t];s||(s=[]),s.push(n),this.retainedEventArguments[t]=s}return}i.forEach(s=>s(n))}hasListeners(t){var n;return!!(!((n=this.listeners[t])===null||n===void 0)&&n.length)}registerWindowListener(t,n){this.windowListeners[n]={registered:!1,windowEventName:t,pluginEventName:n,handler:r=>{this.notifyListeners(n,r)}}}unimplemented(t="not implemented"){return new le.Exception(t,ye.Unimplemented)}unavailable(t="not available"){return new le.Exception(t,ye.Unavailable)}async removeListener(t,n){const r=this.listeners[t];if(!r)return;const i=r.indexOf(n);this.listeners[t].splice(i,1),this.listeners[t].length||this.removeWindowListener(this.windowListeners[t])}addWindowListener(t){window.addEventListener(t.windowEventName,t.handler),t.registered=!0}removeWindowListener(t){t&&(window.removeEventListener(t.windowEventName,t.handler),t.registered=!1)}sendRetainedArgumentsForEvent(t){const n=this.retainedEventArguments[t];n&&(delete this.retainedEventArguments[t],n.forEach(r=>{this.notifyListeners(t,r)}))}}const kn=e=>encodeURIComponent(e).replace(/%(2[346B]|5E|60|7C)/g,decodeURIComponent).replace(/[()]/g,escape),Ln=e=>e.replace(/(%[\dA-F]{2})+/gi,decodeURIComponent);class Bc extends Ht{async getCookies(){const t=document.cookie,n={};return t.split(";").forEach(r=>{if(r.length<=0)return;let[i,s]=r.replace(/=/,"CAP_COOKIE").split("CAP_COOKIE");i=Ln(i).trim(),s=Ln(s).trim(),n[i]=s}),n}async setCookie(t){try{const n=kn(t.key),r=kn(t.value),i=t.expires?`; expires=${t.expires.replace("expires=","")}`:"",s=(t.path||"/").replace("path=",""),o=t.url!=null&&t.url.length>0?`domain=${t.url}`:"";document.cookie=`${n}=${r||""}${i}; path=${s}; ${o};`}catch(n){return Promise.reject(n)}}async deleteCookie(t){try{document.cookie=`${t.key}=; Max-Age=0`}catch(n){return Promise.reject(n)}}async clearCookies(){try{const t=document.cookie.split(";")||[];for(const n of t)document.cookie=n.replace(/^ +/,"").replace(/=.*/,`=;expires=${new Date().toUTCString()};path=/`)}catch(t){return Promise.reject(t)}}async clearAllCookies(){try{await this.clearCookies()}catch(t){return Promise.reject(t)}}}X("CapacitorCookies",{web:()=>new Bc});const Nc=async e=>new Promise((t,n)=>{const r=new FileReader;r.onload=()=>{const i=r.result;t(i.indexOf(",")>=0?i.split(",")[1]:i)},r.onerror=i=>n(i),r.readAsDataURL(e)}),xc=(e={})=>{const t=Object.keys(e);return Object.keys(e).map(i=>i.toLocaleLowerCase()).reduce((i,s,o)=>(i[s]=e[t[o]],i),{})},Mc=(e,t=!0)=>e?Object.entries(e).reduce((r,i)=>{const[s,o]=i;let a,c;return Array.isArray(o)?(c="",o.forEach(l=>{a=t?encodeURIComponent(l):l,c+=`${s}=${a}&`}),c.slice(0,-1)):(a=t?encodeURIComponent(o):o,c=`${s}=${a}`),`${r}&${c}`},"").substr(1):null,$c=(e,t={})=>{const n=Object.assign({method:e.method||"GET",headers:e.headers},t),i=xc(e.headers)["content-type"]||"";if(typeof e.data=="string")n.body=e.data;else if(i.includes("application/x-www-form-urlencoded")){const s=new URLSearchParams;for(const[o,a]of Object.entries(e.data||{}))s.set(o,a);n.body=s.toString()}else if(i.includes("multipart/form-data")||e.data instanceof FormData){const s=new FormData;if(e.data instanceof FormData)e.data.forEach((a,c)=>{s.append(c,a)});else for(const a of Object.keys(e.data))s.append(a,e.data[a]);n.body=s;const o=new Headers(n.headers);o.delete("content-type"),n.headers=o}else(i.includes("application/json")||typeof e.data=="object")&&(n.body=JSON.stringify(e.data));return n};class Uc extends Ht{async request(t){const n=$c(t,t.webFetchExtra),r=Mc(t.params,t.shouldEncodeUrlParams),i=r?`${t.url}?${r}`:t.url,s=await fetch(i,n),o=s.headers.get("content-type")||"";let{responseType:a="text"}=s.ok?t:{};o.includes("application/json")&&(a="json");let c,l;switch(a){case"arraybuffer":case"blob":l=await s.blob(),c=await Nc(l);break;case"json":c=await s.json();break;case"document":case"text":default:c=await s.text()}const d={};return s.headers.forEach((f,p)=>{d[p]=f}),{data:c,headers:d,status:s.status,url:s.url}}async get(t){return this.request(Object.assign(Object.assign({},t),{method:"GET"}))}async post(t){return this.request(Object.assign(Object.assign({},t),{method:"POST"}))}async put(t){return this.request(Object.assign(Object.assign({},t),{method:"PUT"}))}async patch(t){return this.request(Object.assign(Object.assign({},t),{method:"PATCH"}))}async delete(t){return this.request(Object.assign(Object.assign({},t),{method:"DELETE"}))}}X("CapacitorHttp",{web:()=>new Uc});var Bn;(function(e){e.Dark="DARK",e.Light="LIGHT",e.Default="DEFAULT"})(Bn||(Bn={}));var Nn;(function(e){e.StatusBar="StatusBar",e.NavigationBar="NavigationBar"})(Nn||(Nn={}));class jc extends Ht{async setStyle(){this.unavailable("not available for web")}async setAnimation(){this.unavailable("not available for web")}async show(){this.unavailable("not available for web")}async hide(){this.unavailable("not available for web")}}X("SystemBars",{web:()=>new jc});const ke=X("App",{web:()=>be(()=>import("./web-BwEbFFby.js"),[]).then(e=>new e.AppWeb)});var ve;(function(e){e.Heavy="HEAVY",e.Medium="MEDIUM",e.Light="LIGHT"})(ve||(ve={}));var Ct;(function(e){e.Success="SUCCESS",e.Warning="WARNING",e.Error="ERROR"})(Ct||(Ct={}));const Ie=X("Haptics",{web:()=>be(()=>import("./web-DFf1pXpw.js"),[]).then(e=>new e.HapticsWeb)}),Vt=X("Device",{web:()=>be(()=>import("./web-Daf5pae4.js"),[]).then(e=>new e.DeviceWeb)});var xn;(function(e){e[e.Sunday=1]="Sunday",e[e.Monday=2]="Monday",e[e.Tuesday=3]="Tuesday",e[e.Wednesday=4]="Wednesday",e[e.Thursday=5]="Thursday",e[e.Friday=6]="Friday",e[e.Saturday=7]="Saturday"})(xn||(xn={}));const M=X("LocalNotifications",{web:()=>be(()=>import("./web-DRq7HyNt.js"),[]).then(e=>new e.LocalNotificationsWeb)}),j=X("PushNotifications",{});X("Network",{web:()=>be(()=>import("./web-yMB36t1Z.js"),[]).then(e=>new e.NetworkWeb)});const D=!!(le&&le.isNativePlatform&&le.isNativePlatform());console.log("[FileFusion App] Initialized. Native Platform:",D);function H(e,t=2e3,n=null){return Promise.race([e,new Promise(r=>setTimeout(()=>r(n),t))])}function Hc(e){const t="=".repeat((4-e.length%4)%4),n=(e+t).replace(/\-/g,"+").replace(/_/g,"/"),r=window.atob(n),i=new Uint8Array(r.length);for(let s=0;s<r.length;++s)i[s]=r.charCodeAt(s);return i}function v(e=""){let t="";if(typeof window<"u"){const r=window.location.pathname,i=r.indexOf("/panel"),s=r.indexOf("/public");s!==-1?t=window.location.origin+r.substring(0,s+7):i>0?t=window.location.origin+r.substring(0,i):t=window.location.origin}if(!e)return t;const n=e.startsWith("/")?e:"/"+e;return t+n}function Vc(){var t;return((t=document.querySelector('meta[name="sw-url"]'))==null?void 0:t.getAttribute("content"))||v("/sw.js")}async function Mn(){if(D)try{const t=await H(Vt.getId(),1500,null);if(t&&t.identifier)return t.identifier}catch{}let e=localStorage.getItem("ff_device_uuid");return e||(e="dev_"+Math.random().toString(36).substring(2,15)+"_"+Date.now().toString(36),localStorage.setItem("ff_device_uuid",e)),e}async function Le(){if(D){try{const t=await H(Vt.getInfo(),1500,null);if(t&&t.model)return(t.manufacturer?t.manufacturer+" ":"")+t.model}catch{}return"Android Mobile"}if(typeof navigator>"u")return"Unknown Device";if(navigator.userAgentData&&navigator.userAgentData.platform){const t=navigator.userAgentData.platform;if(/Windows/i.test(t))return"Windows PC";if(/macOS/i.test(t))return"MacBook / macOS";if(/Android/i.test(t))return"Android Device";if(/iOS|iPhone|iPad/i.test(t))return"Apple iOS Device";if(/Linux/i.test(t))return"Linux PC"}const e=navigator.userAgent||"";if(/iPhone/i.test(e))return"iPhone";if(/iPad/i.test(e))return"iPad";if(/Macintosh|Mac OS X/i.test(e))return"MacBook / macOS";if(/Windows NT 10.0/i.test(e))return"Windows 10/11 PC";if(/Windows/i.test(e))return"Windows PC";if(/Android/i.test(e)){const t=e.match(/Android[^;]+;\s*([^;)]+)\)/);if(t&&t[1]){const n=t[1].replace(/Build\/.+$/,"").trim();if(n&&n.length<30)return n}return"Android Mobile"}return/Linux/i.test(e)?"Linux Workstation":"Web Browser"}function Wr(e){if(!(!e||!e.mode)){if(console.log("[FileFusion App] Processing Android Share Sheet Intent:",e),e.mode==="text"&&e.text){const t=e.text.match(/https?:\/\/[^\s]+/),n=t?t[0]:e.text,r=n.startsWith("http://")||n.startsWith("https://"),i=document.getElementById("url_field")||document.querySelector('input[name="url"]');i?(i.value=n,i.dispatchEvent(new Event("input",{bubbles:!0})),i.focus()):r?window.location.href=v("/panel/add-links?prefill_url="+encodeURIComponent(n)+"&prefill_name="+encodeURIComponent(e.text!==n?e.text:"")):window.location.href=v("/panel/newfile?prefill_content="+encodeURIComponent(e.text))}else if(e.mode==="file"||e.mode==="files"){const t=document.querySelector('[data-modal-target="uploadModal"]')||document.querySelector("#upload-btn");t?t.click():window.location.pathname.includes("/panel/upload")||(window.location.href=v("/panel/upload?intent=share"))}}}window.addEventListener("filefusion:android-share",function(e){Wr(e.detail||{})});typeof window<"u"&&window.__FILEFUSION_PENDING_SHARE__&&setTimeout(()=>{Wr(window.__FILEFUSION_PENDING_SHARE__),window.__FILEFUSION_PENDING_SHARE__=null},300);function qc(){try{if(typeof window.FileFusionSoundBridge<"u"&&typeof window.FileFusionSoundBridge.playNotificationSound=="function"){window.FileFusionSoundBridge.playNotificationSound();return}const e=window.AudioContext||window.webkitAudioContext;if(e){const t=new e,n=t.currentTime,r=t.createOscillator(),i=t.createGain();r.type="sine",r.frequency.setValueAtTime(587.33,n),i.gain.setValueAtTime(.3,n),i.gain.exponentialRampToValueAtTime(.01,n+.22),r.connect(i),i.connect(t.destination),r.start(n),r.stop(n+.22);const s=t.createOscillator(),o=t.createGain();s.type="sine",s.frequency.setValueAtTime(880,n+.1),o.gain.setValueAtTime(.3,n+.1),o.gain.exponentialRampToValueAtTime(.01,n+.4),s.connect(o),o.connect(t.destination),s.start(n+.1),s.stop(n+.4)}}catch(e){console.warn("[FileFusion Sound]:",e)}}D&&[{id:"filefusion_high_priority_v2",name:"FileFusion Alerts & Security",description:"Audible alerts, task reminders and vault security notifications",importance:5,visibility:1,sound:"default",vibration:!0,lights:!0,lightColor:"#6366f1"},{id:"filefusion_default_channel",name:"FileFusion Notifications",description:"General alerts, task reminders and system notifications",importance:5,visibility:1,sound:"default",vibration:!0,lights:!0,lightColor:"#6366f1"},{id:"filefusion_transfers",name:"File Transfers & Uploads",description:"Progress and completion notifications for encrypted uploads",importance:5,visibility:1,sound:"default",vibration:!0,lights:!0,lightColor:"#10b981"},{id:"filefusion_security",name:"Security & Vault Alerts",description:"Critical authentication and vault security events",importance:5,visibility:1,sound:"default",vibration:!0,lights:!0,lightColor:"#f43f5e"},{id:"fcm_default_channel",name:"Firebase Notifications",description:"System push notifications",importance:5,visibility:1,sound:"default",vibration:!0,lights:!0,lightColor:"#6366f1"}].forEach(t=>{typeof M<"u"&&M.createChannel&&H(M.createChannel(t),1500).catch(n=>console.warn(`[FileFusion App] Local Channel ${t.id}:`,n)),typeof j<"u"&&j.createChannel&&H(j.createChannel(t),1500).catch(n=>console.warn(`[FileFusion App] Push Channel ${t.id}:`,n))});const ie={fileUpload:(e,t,n)=>({title:"📁 File Upload Completed",body:`${e||"Your file"} (${t||"encrypted"}) has been securely stored in your Vault.`,channelId:"filefusion_transfers",url:n||v("/panel/files")}),vaultUnlocked:(e,t)=>({title:"🛡️ Security Alert",body:`Master Vault unlocked on ${e||"current device"}. Session active for 30 mins.`,channelId:"filefusion_security",url:t||v("/panel/hidden-files")}),storageWarning:(e,t,n,r)=>({title:"⚠️ Storage Warning",body:`Storage capacity reached ${e||"88%"} (${t||"4.4 GB"} of ${n||"5.0 GB"} used).`,channelId:"filefusion_transfers",url:r||v("/panel/storage-cleaner")}),linkSaved:(e,t,n)=>({title:"🔗 Link Saved",body:`Saved '${e||"Bookmark"}' to your ${t||"Dev & Cloud"} category.`,channelId:"filefusion_transfers",url:n||v("/panel/linklist")}),taskDue:(e,t,n)=>({title:"📋 Task Reminder",body:`Action Item: '${e||"Review build"}' is due ${t||"in 30 mins"}.`,channelId:"filefusion_transfers",url:n||v("/panel/todos")}),backupCompleted:(e,t,n)=>({title:"🔄 Backup Completed",body:`System backup '${e||"filefusion_backup.zip"}' uploaded to ${t||"Cloudflare R2"}.`,channelId:"filefusion_transfers",url:n||v("/panel/admin/backups")})};async function ht(e){var t;try{const n=(t=document.querySelector('meta[name="csrf-token"]'))==null?void 0:t.getAttribute("content");return await(await fetch(v("/devices/register-push"),{method:"POST",headers:{"Content-Type":"application/json","X-CSRF-TOKEN":n||"",Accept:"application/json"},body:JSON.stringify(e)})).json()}catch(n){return console.warn("[FileFusion Push] Failed to register push token with server:",n),null}}window.FileFusionNative={isNative:D,Capacitor:le,Device:Vt,LocalNotifications:M,PushNotifications:j,getAppUrl:v,getDeviceName:Le,getDeviceUuid:Mn,templates:ie,biometrics:{isAvailable:async function(){if(typeof window.FileFusionBiometrics<"u"&&typeof window.FileFusionBiometrics.checkBiometricStatus=="function")try{return window.FileFusionBiometrics.checkBiometricStatus()==="available"}catch{return!1}if(D&&window.PublicKeyCredential&&typeof window.PublicKeyCredential.isUserVerifyingPlatformAuthenticatorAvailable=="function")try{return await window.PublicKeyCredential.isUserVerifyingPlatformAuthenticatorAvailable()}catch{return!1}return!1},prompt:function(e={}){return new Promise(t=>{const n=e.title||"Unlock FileFusion Vault",r=e.subtitle||"Touch the fingerprint sensor to verify identity";if(typeof window.FileFusionBiometrics<"u"&&typeof window.FileFusionBiometrics.authenticate=="function"){const i="ff_bio_cb_"+Math.floor(Math.random()*1e6);window[i]=function(s){delete window[i],t(s)};try{window.FileFusionBiometrics.authenticate(n,r,i)}catch(s){delete window[i],t({success:!1,message:s.message})}return}if(window.PublicKeyCredential){t({success:!0,message:"Platform verified"});return}t({success:!1,message:"Biometrics not supported on this device"})})},unlockVault:async function(e="files"){var r;if(!await this.isAvailable()&&typeof window.FileFusionBiometrics>"u")return window.toast&&window.toast("Fingerprint sensor is not available on this device.","warning"),!1;const n=await this.prompt({title:"Unlock FileFusion Vault",subtitle:"Touch the fingerprint sensor to continue"});if(!n.success)return n.message&&!n.message.toLowerCase().includes("cancel")&&window.toast&&window.toast("Biometric authentication failed: "+n.message,"error"),!1;try{const i=(r=document.querySelector('meta[name="csrf-token"]'))==null?void 0:r.getAttribute("content"),o=await(await fetch(v("/panel/vault/biometric-unlock"),{method:"POST",headers:{"Content-Type":"application/json","X-CSRF-TOKEN":i||"",Accept:"application/json"},body:JSON.stringify({vault_type:e,device_name:await Le(),platform:D?"android":"web"})})).json();return o.ok?(window.toast&&window.toast("Vault unlocked with Fingerprint!","success"),o.redirect&&setTimeout(()=>{window.location.href=o.redirect},300),!0):(window.toast&&window.toast(o.info||o.error||"Biometric authentication failed.","error"),!1)}catch(i){return console.error("[Biometric Vault Unlock Error]:",i),window.toast&&window.toast("Network error during biometric verification.","error"),!1}}},checkPermissions:async function(){if(D)try{const e=await H(M.checkPermissions(),2e3,null);if(e&&e.display)return e.display}catch(e){console.error("[FileFusion Native] checkPermissions error:",e)}return"Notification"in window?Notification.permission:"unsupported"},requestPermissions:async function(){let e="unsupported";if(D)try{const t=await H(M.requestPermissions(),5e3,null);t&&t.display&&(e=t.display)}catch(t){console.error("[FileFusion Native] requestPermissions error:",t)}else if("Notification"in window)try{e=await Notification.requestPermission()}catch(t){console.error("[Notification.requestPermission Error]:",t)}return e==="granted"&&this.registerPushNotifications().catch(t=>{console.warn("[FileFusion Push Register Background Error]:",t)}),e},reregisterPushNotifications:async function(){if(console.log("[FileFusion] Force re-registering push credentials..."),!D&&"serviceWorker"in navigator&&"PushManager"in window)try{const t=await(await navigator.serviceWorker.ready).pushManager.getSubscription();t&&(await t.unsubscribe(),console.log("[FileFusion] Unsubscribed stale VAPID subscription"))}catch(e){console.warn("[FileFusion] Error unsubscribing:",e)}return await this.registerPushNotifications()},registerPushNotifications:async function(){var n,r;const e=await Mn(),t=await Le();if(D){await ht({device_uuid:e,device_name:t,platform:"android",push_type:"fcm",push_token:"android_native_"+e});try{if(typeof j<"u"&&j.register){const i=await H(j.requestPermissions(),2500,null);i&&(i.receive==="granted"||i.display==="granted")&&(await H(j.register(),3e3,null),j.addListener("registration",async s=>{s&&s.value&&(console.log("[FileFusion FCM] Registration Token:",s.value),await ht({device_uuid:e,device_name:t,platform:"android",push_type:"fcm",push_token:s.value}))}),j.addListener("pushNotificationActionPerformed",s=>{var c,l;console.log("[FileFusion Push Action Performed]:",s);const a=(((c=s.notification)==null?void 0:c.data)||{}).url||((l=s.notification)==null?void 0:l.click_action)||"/panel";if(a){const d=a.startsWith("http")?a:v(a);window.location.href=d}}),j.addListener("pushNotificationReceived",async s=>{var o;if(console.log("[FileFusion Push Received]:",s),qc(),typeof M<"u"&&M.schedule)try{await M.schedule({notifications:[{id:Math.floor(Math.random()*1e6),title:s.title||"FileFusion Alert",body:s.body||"",channelId:((o=s.data)==null?void 0:o.channelId)||"filefusion_high_priority_v2",sound:"default",smallIcon:"ic_stat_filefusion",iconColor:"#6366f1",extra:s.data||{}}]})}catch(a){console.warn("[FileFusion Push Local Notification Error]:",a)}}),j.addListener("registrationError",s=>{console.warn("[FileFusion FCM Registration Error]:",s)}))}typeof M<"u"&&M.addListener&&M.addListener("localNotificationActionPerformed",i=>{var a;console.log("[FileFusion Local Notification Action]:",i);const o=(((a=i.notification)==null?void 0:a.extra)||{}).url||"/panel";if(o){const c=o.startsWith("http")?o:v(o);window.location.href=c}})}catch(i){console.warn("[FileFusion FCM Registration Error]:",i)}return}if("serviceWorker"in navigator&&"PushManager"in window)try{let i=await navigator.serviceWorker.getRegistration();if(!i)try{i=await navigator.serviceWorker.register(Vc())}catch(a){console.warn("[FileFusion ServiceWorker Register]:",a)}i||(i=await H(navigator.serviceWorker.ready,2500,null));const s=await H(fetch(v("/devices/vapid-public-key")),3e3,null);if(!s){console.warn("[FileFusion VAPID] Could not fetch VAPID key from server.");return}const o=await s.json();if(o.ok&&o.publicKey&&i&&i.pushManager){let a=await i.pushManager.getSubscription();if(a||(a=await i.pushManager.subscribe({userVisibleOnly:!0,applicationServerKey:Hc(o.publicKey)})),a){const c=a.toJSON();await ht({device_uuid:e,device_name:t,platform:window.matchMedia("(display-mode: standalone)").matches?"pwa":"web",push_type:"vapid",endpoint:c.endpoint,public_key:(n=c.keys)==null?void 0:n.p256dh,auth_token:(r=c.keys)==null?void 0:r.auth,push_token:JSON.stringify(c)}),console.log("[FileFusion VAPID] Web Push subscription synced with server.")}}}catch(i){console.warn("[FileFusion VAPID Subscription Error]:",i)}},notify:async function(e){const t=typeof e=="string"?e:e.title||"FileFusion",n=typeof e=="object"?e.body||"":arguments[1]||"",r=typeof e=="object"&&e.channelId?e.channelId:"filefusion_transfers",i=typeof e=="object"&&e.delay?parseInt(e.delay,10):0,s=typeof e=="object"&&e.url?e.url:v("/panel"),o=Math.floor(Date.now()%1e6);if(D)try{const a={title:t,body:n,id:o,channelId:r,group:r||"filefusion_alerts",smallIcon:"ic_stat_filefusion",extra:{url:s}};return i>0&&(a.schedule={at:new Date(Date.now()+i*1e3),allowWhileIdle:!0}),await H(M.schedule({notifications:[a]}),3e3),{success:!0,mode:"native",id:o,delay:i}}catch(a){console.error("[FileFusion Native] schedule notification error:",a)}if("Notification"in window&&Notification.permission==="granted"){const a={body:n,tag:r||"filefusion_alerts",icon:v("/favicon.ico"),badge:v("/favicon.ico"),vibrate:[200,100,200],data:{url:s}};if("serviceWorker"in navigator)try{let c=await navigator.serviceWorker.getRegistration();if(c||(c=await H(navigator.serviceWorker.ready,2e3,null)),c&&c.showNotification)return i>0?setTimeout(()=>c.showNotification(t,a),i*1e3):await c.showNotification(t,a),{success:!0,mode:"pwa_sw",id:o,delay:i}}catch(c){console.warn("[FileFusion PWA SW Notification]:",c)}try{return i>0?setTimeout(()=>new Notification(t,a),i*1e3):new Notification(t,a),{success:!0,mode:"web",id:o,delay:i}}catch(c){console.warn("[FileFusion Web Notification]:",c)}}return window.ff&&typeof window.ff.toast=="function"?window.ff.toast(t+": "+n,"info"):window.toast&&window.toast(t+": "+n),{success:!0,mode:"toast",id:o,delay:0}},notifyPreset:async function(e,t={},n=0){const r=await Le();let i=null;return e==="fileUpload"?i=ie.fileUpload(t.filename,t.size):e==="vaultUnlocked"?i=ie.vaultUnlocked(t.deviceName||r):e==="storageWarning"?i=ie.storageWarning(t.percent,t.used,t.quota):e==="linkSaved"?i=ie.linkSaved(t.title,t.category):e==="taskDue"?i=ie.taskDue(t.title,t.dueText):e==="backupCompleted"?i=ie.backupCompleted(t.archiveName,t.destination):i={title:t.title||"FileFusion",body:t.body||""},i.delay=n,await this.notify(i)},share:async function(e={}){const t=e.title||"FileFusion",n=e.text||"",r=e.url||window.location.href,i=e.dialogTitle||"Share with FileFusion";if(D)try{const{Share:o}=await be(async()=>{const{Share:a}=await import("./index-DlWUghtb.js");return{Share:a}},[]);return await o.share({title:t,text:n,url:r,dialogTitle:i}),{success:!0,mode:"native"}}catch(o){console.warn("[FileFusion Native Share Warning]:",o)}if(typeof navigator<"u"&&navigator.share)try{return await navigator.share({title:t,text:n,url:r}),{success:!0,mode:"web_share"}}catch(o){o.name!=="AbortError"&&console.warn("[FileFusion Web Share Warning]:",o)}try{if(window.ff&&typeof window.ff.copy=="function")window.ff.copy(r,"Link copied to clipboard!");else if(navigator.clipboard&&window.isSecureContext)await navigator.clipboard.writeText(r),window.toast&&window.toast.success("Link copied to clipboard!");else{var s=document.createElement("textarea");s.value=r,s.style.position="fixed",s.style.top="0",s.style.left="-9999px",document.body.appendChild(s),s.focus(),s.select(),document.execCommand("copy"),document.body.removeChild(s),window.toast&&window.toast.success("Link copied to clipboard!")}return{success:!0,mode:"clipboard"}}catch{return prompt("Copy link to share:",r),{success:!0,mode:"prompt"}}}};typeof window<"u"&&"Notification"in window&&Notification.permission==="granted"&&setTimeout(()=>{var e;(e=window.FileFusionNative)==null||e.registerPushNotifications().catch(()=>{})},2e3);(function(){document.addEventListener("click",function(e){const t=e.target.closest("a");if(!t||!t.href)return;if((t.hasAttribute("download")||t.href.includes("/download/file/")||t.href.includes("/download/bulk")||t.href.includes("/download/")||t.classList.contains("ff-download-link"))&&window.FileFusionAndroidDownload&&typeof window.FileFusionAndroidDownload.download=="function"){e.preventDefault(),e.stopPropagation();let r=t.getAttribute("data-filename")||t.getAttribute("download")||"";if(!r||r.trim().toLowerCase()==="download"){const i=t.closest(".ff-tile, .ff-list-row, .ff-card");if(i){const s=i.querySelector(".ff-tile-name, .ff-list-title");s&&s.innerText.trim()&&(r=s.innerText.trim())}}return window.FileFusionAndroidDownload.download(t.href,r.trim()),window.ff&&typeof window.ff.toast=="function"&&window.ff.toast(`Downloading ${r||"file"} to Downloads/FileFusion...`,"info",2500),!1}},!0)})();(function(){if(typeof window<"u"){const e=async()=>{try{(D||"Notification"in window&&Notification.permission==="granted")&&window.FileFusionNative&&typeof window.FileFusionNative.registerPushNotifications=="function"&&await window.FileFusionNative.registerPushNotifications()}catch(t){console.warn("[FileFusion Push AutoSync]:",t)}};document.readyState==="loading"?document.addEventListener("DOMContentLoaded",e):e()}})();(function(){let e=0;D&&ke&&typeof ke.addListener=="function"&&ke.addListener("backButton",function(){const n=document.getElementById("ffGlobalBottomSheetBackdrop");if(n&&n.classList.contains("is-open")){window.ff&&window.ff.closeBottomSheet&&window.ff.closeBottomSheet(),window.ff&&window.ff.haptic&&window.ff.haptic("selection");return}const r=Array.from(document.querySelectorAll(".ff-modal-overlay, #sharemodal, #deletemodal, #editmodal, #shareLinkModal, #sharePasswordModal, #shareCategoryModal, .ff-share-modal-root")).filter(l=>window.getComputedStyle(l).display!=="none"&&l.style.display!=="none"&&!l.hidden);if(r.length>0){const l=r[r.length-1],d=l.querySelector('.ff-modal-close, .closeShareLinkModalBtn, .closeSharePasswordModalBtn, .closeShareCategoryModalBtn, [onclick*="hide"]');d?d.click():l.style.display="none",window.ff&&window.ff.haptic&&window.ff.haptic("selection");return}if(Array.from(document.querySelectorAll("[data-ff-menu-panel]")).filter(l=>!l.hidden).length>0){window.ff&&window.ff.closeAllMenus&&window.ff.closeAllMenus();return}const s=document.getElementById("ffShell");if(s&&s.classList.contains("is-nav-open")){s.classList.remove("is-nav-open");const l=document.getElementById("ffBackdrop");l&&(l.hidden=!0);return}const o=window.location.pathname;if(!(o==="/panel"||o==="/panel/"||o==="/"||o.endsWith("/dashboard"))){window.history.length>1?window.history.back():window.location.href=v("/panel");return}const c=Date.now();c-e<2e3?ke.exitApp():(e=c,window.ff&&window.ff.toast&&window.ff.toast("Press back again to exit","info",1800),window.ff&&window.ff.haptic&&window.ff.haptic("light"))});async function t(n="light"){if(window.FileFusionAndroidHaptics&&typeof window.FileFusionAndroidHaptics.vibrate=="function")try{window.FileFusionAndroidHaptics.vibrate(n);return}catch{}if(D&&Ie)try{if(n==="light"||n==="selection"){await Ie.impact({style:ve.Light});return}else if(n==="medium"){await Ie.impact({style:ve.Medium});return}else if(n==="heavy"){await Ie.impact({style:ve.Heavy});return}else if(n==="error"||n==="warning"){await Ie.notification({type:Ct.Error});return}}catch{}try{navigator.vibrate&&(n==="light"||n==="selection"?navigator.vibrate(20):n==="medium"?navigator.vibrate(45):(n==="heavy"||n==="error")&&navigator.vibrate([40,50,40]))}catch{}}window.ffTriggerHaptic=t,document.addEventListener("click",function(n){n.target.closest(".ff-bottom-nav-item, .copy-link-btn, .copy-username-btn, #copyPasswordBtn, [data-ff-copy], .ff-quick-btn, .ff-chip, .ff-btn")&&t("selection")},{passive:!0})})();export{ve as I,Ct as N,Ht as W,be as _,X as r};
