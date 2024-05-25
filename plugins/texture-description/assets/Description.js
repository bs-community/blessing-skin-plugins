!function(t){"use strict";let e;function n(){}function r(t){return t()}function o(){return Object.create(null)}function i(t){t.forEach(r)}function c(t){return"function"==typeof t}function l(t,e){return t!=t?e==e:t!==e||t&&"object"==typeof t||"function"==typeof t}function a(t,e){t.appendChild(e)}function u(t,e,n){t.insertBefore(e,n||null)}function d(t){t.parentNode&&t.parentNode.removeChild(t)}function s(t){return document.createElement(t)}function f(t){return document.createTextNode(t)}function p(){return f(" ")}function m(t,e,n,r){return t.addEventListener(e,n,r),()=>t.removeEventListener(e,n,r)}function h(t,e,n){null==n?t.removeAttribute(e):t.getAttribute(e)!==n&&t.setAttribute(e,n)}function $(t,e){t.value=null==e?"":e}let g=[],x=[],b=[],y=[],v=Promise.resolve(),_=!1;function w(t){b.push(t)}let E=new Set,k=0;function L(){if(0!==k)return;let t=e;do{try{for(;k<g.length;){let t=g[k];k++,e=t,function(t){if(null!==t.fragment){t.update(),i(t.before_update);let e=t.dirty;t.dirty=[-1],t.fragment&&t.fragment.p(t.ctx,e),t.after_update.forEach(w)}}(t.$$)}}catch(t){throw g.length=0,k=0,t}for(e=null,g.length=0,k=0;x.length;)x.pop()();for(let t=0;t<b.length;t+=1){let e=b[t];E.has(e)||(E.add(e),e())}b.length=0}while(g.length);for(;y.length;)y.pop()();_=!1,E.clear(),e=t}let N=new Set;class T{$destroy(){!function(t,e){let n=t.$$;null!==n.fragment&&(i(n.on_destroy),n.fragment&&n.fragment.d(1),n.on_destroy=n.fragment=null,n.ctx=[])}(this,0),this.$destroy=n}$on(t,e){if(!c(e))return n;let r=this.$$.callbacks[t]||(this.$$.callbacks[t]=[]);return r.push(e),()=>{let t=r.indexOf(e);-1!==t&&r.splice(t,1)}}$set(t){this.$$set&&0!==Object.keys(t).length&&(this.$$.skip_bound=!0,this.$$set(t),this.$$.skip_bound=!1)}}function j(t){!function(t,e,n){let r=function(t){if(!t)return document;let e=t.getRootNode?t.getRootNode():t.ownerDocument;return e&&e.host?e:t.ownerDocument}(t);if(!r.getElementById(e)){let t=s("style");t.id=e,t.textContent=n,a(r.head||r,t),t.sheet}}(t,"svelte-a377xa","description-content.svelte-a377xa h1,description-content.svelte-a377xa h2{padding-bottom:0.3rem;border-bottom:1px solid #eaecef}description-content.svelte-a377xa blockquote{color:#aaa;border-left:0.3rem solid #aaa}description-content.svelte-a377xa hr{border-top-width:4px}")}function C(e){let n,r,o,i,c,l,f,m;let $=e[0]&&!e[4]&&M(e);function g(t,e){return t[4]?D:t[7]?H:A}let x=g(e),b=x(e),y=e[4]&&O(e);return{c(){n=s("div"),r=s("div"),o=s("div"),(i=s("h3")).textContent=`${t.t("texture-description.description")}`,c=p(),$&&$.c(),l=p(),f=s("div"),b.c(),m=p(),y&&y.c(),h(i,"class","card-title"),h(o,"class","d-flex justify-content-between align-items-center"),h(r,"class","card-header"),h(f,"class","card-body"),h(n,"class","card card-secondary")},m(t,e){u(t,n,e),a(n,r),a(r,o),a(o,i),a(o,c),$&&$.m(o,null),a(n,l),a(n,f),b.m(f,null),a(n,m),y&&y.m(n,null)},p(t,e){t[0]&&!t[4]?$?$.p(t,e):(($=M(t)).c(),$.m(o,null)):$&&($.d(1),$=null),x===(x=g(t))&&b?b.p(t,e):(b.d(1),(b=x(t))&&(b.c(),b.m(f,null))),t[4]?y?y.p(t,e):((y=O(t)).c(),y.m(n,null)):y&&(y.d(1),y=null)},d(t){t&&d(n),$&&$.d(),b.d(),y&&y.d()}}}function M(e){let r,o,i,c;return{c(){r=s("button"),h(o=s("i"),"class","fas fa-edit"),h(r,"class","btn btn-secondary btn-sm float-right"),h(r,"title",t.t("texture-description.edit"))},m(t,n){u(t,r,n),a(r,o),i||(c=m(r,"click",e[8]),i=!0)},p:n,d(t){t&&d(r),i=!1,c()}}}function A(t){let e;return{c(){var t,n,r;r="svelte-a377xa",(n="class")in(t=e=s("description-content"))?t[n]=(t[n],r):h(t,n,r)},m(n,r){u(n,e,r),e.innerHTML=t[2]},p(t,n){4&n&&(e.innerHTML=t[2])},d(t){t&&d(e)}}}function H(e){let r,o;return{c(){r=s("p"),(o=s("i")).textContent=`${t.t("texture-description.empty")}`},m(t,e){u(t,r,e),a(r,o)},p:n,d(t){t&&d(r)}}}function D(t){let e,n,r;return{c(){h(e=s("textarea"),"class","form-control"),h(e,"rows","10")},m(o,i){u(o,e,i),$(e,t[3]),n||(r=m(e,"input",t[12]),n=!0)},p(t,n){8&n&&$(e,t[3])},d(t){t&&d(e),n=!1,r()}}}function O(e){let n,r,o,c,l,$,g,x,b,y;let v=t.t("general.cancel")+"",_=e[6]&&S(e),w=e[5]?B:q,E=w(e);return{c(){n=s("div"),_&&_.c(),r=p(),o=s("div"),c=s("button"),E.c(),$=p(),g=s("button"),x=f(v),h(c,"class","btn btn-primary"),c.disabled=l=e[5]||e[6],h(g,"class","btn btn-secondary"),g.disabled=e[5],h(o,"class","d-flex justify-content-between"),h(n,"class","card-footer")},m(t,i){u(t,n,i),_&&_.m(n,null),a(n,r),a(n,o),a(o,c),E.m(c,null),a(o,$),a(o,g),a(g,x),b||(y=[m(c,"click",e[9]),m(g,"click",e[13])],b=!0)},p(t,e){t[6]?_?_.p(t,e):((_=S(t)).c(),_.m(n,r)):_&&(_.d(1),_=null),w===(w=t[5]?B:q)&&E?E.p(t,e):(E.d(1),(E=w(t))&&(E.c(),E.m(c,null))),96&e&&l!==(l=t[5]||t[6])&&(c.disabled=l),32&e&&(g.disabled=t[5])},d(t){t&&d(n),_&&_.d(),E.d(),b=!1,i(y)}}}function S(e){let n,r;let o=t.t("texture-description.exceeded",{max:e[1]})+"";return{c(){n=s("div"),r=f(o),h(n,"class","alert alert-info")},m(t,e){u(t,n,e),a(n,r)},p(e,n){if(2&n&&o!==(o=t.t("texture-description.exceeded",{max:e[1]})+"")){var i,c;i=r,c=""+(c=o),i.wholeText!==c&&(i.data=c)}},d(t){t&&d(n)}}}function q(e){let r,o=t.t("general.submit")+"";return{c(){r=f(o)},m(t,e){u(t,r,e)},p:n,d(t){t&&d(r)}}}function B(t){let e;return{c(){(e=s("span")).innerHTML='<i class="fas fa-sync fa-spin"></i>'},m(t,n){u(t,e,n)},p:n,d(t){t&&d(e)}}}function I(t){let e;let r=(t[0]||!t[7])&&C(t);return{c(){r&&r.c(),e=f("")},m(t,n){r&&r.m(t,n),u(t,e,n)},p(t,[n]){t[0]||!t[7]?r?r.p(t,n):((r=C(t)).c(),r.m(e.parentNode,e)):r&&(r.d(1),r=null)},i:n,o:n,d(t){r&&r.d(t),t&&d(e)}}}function R(n,r,o){var i,c;let l,a;let u="",d="",{tid:s}=r,{canEdit:f=!1}=r,p=!1,m=!1,{maxLength:h=1/0}=r;async function $(){let e=await t.fetch.get(`/texture/${s}/description`,{raw:!0});if("string"==typeof e)o(3,d=e),o(4,p=!0);else{t.notify.toast.error(e.message);return}}async function g(){o(5,m=!0);let e=await t.fetch.put(`/texture/${s}/description`,{description:d});o(5,m=!1),"string"==typeof e?(o(2,u=e),o(4,p=!1)):t.notify.toast.error(e.message)}c=async()=>{o(2,u=await t.fetch.get(`/texture/${s}/description`))},(function(){if(!e)throw Error("Function called outside component initialization");return e})().$$.on_mount.push(c);let x=()=>o(4,p=!1);return n.$$set=t=>{"tid"in t&&o(10,s=t.tid),"canEdit"in t&&o(0,f=t.canEdit),"maxLength"in t&&o(1,h=t.maxLength)},n.$$.update=()=>{4&n.$$.dirty&&o(7,l=""===u.trim()),2058&n.$$.dirty&&o(6,a=(null!==o(11,i=null==d?void 0:d.length)&&void 0!==i?i:0)>h)},[f,h,u,d,p,m,a,l,$,g,s,i,function(){o(3,d=this.value)},x]}customElements.define("description-content",HTMLDivElement);let z=document.querySelector("#texture-description");if(z){let{dataset:t}=z;new class extends T{constructor(t){super(),function(t,l,a,u,s,f,p,m=[-1]){let h=e;e=t;let $=t.$$={fragment:null,ctx:[],props:f,update:n,not_equal:s,bound:o(),on_mount:[],on_destroy:[],on_disconnect:[],before_update:[],after_update:[],context:new Map(l.context||(h?h.$$.context:[])),callbacks:o(),dirty:m,skip_bound:!1,root:l.target||h.$$.root};p&&p($.root);let x=!1;if($.ctx=a?a(t,l.props||{},(e,n,...r)=>{let o=r.length?r[0]:n;if($.ctx&&s($.ctx[e],$.ctx[e]=o)&&(!$.skip_bound&&$.bound[e]&&$.bound[e](o),x)){var i;-1===(i=t).$$.dirty[0]&&(g.push(i),_||(_=!0,v.then(L)),i.$$.dirty.fill(0)),i.$$.dirty[e/31|0]|=1<<e%31}return n}):[],$.update(),x=!0,i($.before_update),$.fragment=!!u&&u($.ctx),l.target){if(l.hydrate){let t=Array.from(l.target.childNodes);$.fragment&&$.fragment.l(t),t.forEach(d)}else $.fragment&&$.fragment.c();if(l.intro){var b;(b=t.$$.fragment)&&b.i&&(N.delete(b),b.i(void 0))}(function(t,e,n,o){let{fragment:l,after_update:a}=t.$$;l&&l.m(e,n),o||w(()=>{let e=t.$$.on_mount.map(r).filter(c);t.$$.on_destroy?t.$$.on_destroy.push(...e):i(e),t.$$.on_mount=[]}),a.forEach(w)})(t,l.target,l.anchor,l.customElement),L()}e=h}(this,t,R,I,l,{tid:10,canEdit:0,maxLength:1},j)}}({target:z,props:{tid:function(){let{pathname:t}=location,e=/(\d+)$/.exec(t);return e?.[1]??"0"}(),canEdit:"true"===t.canEdit,maxLength:Number.parseInt(t.maxLength??"0")||1/0}})}}(blessing);
