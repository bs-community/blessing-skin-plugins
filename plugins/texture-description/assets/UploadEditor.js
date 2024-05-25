!function(t) {
    "use strict";
    let e;

    function m() {
        if (!e) throw Error("Function called outside component initialization");
        return e;
    }

    let $ = [],
        h = [],
        g = [],
        x = [],
        b = Promise.resolve(),
        y = !1;

    function _(t) {
        g.push(t);
    }

    let v = new Set,
        E = 0;

    function w() {
        if (0 !== E) return;
        let t = e;
        do {
            try {
                for (; E < $.length;) {
                    let t = $[E];
                    E++, e = t, function(t) {
                        if (null !== t.fragment) {
                            t.update(), i(t.before_update);
                            let e = t.dirty;
                            t.dirty = [-1], t.fragment && t.fragment.p(t.ctx, e), t.after_update.forEach(_)
                        }
                    }(t.$$)
                }
            } catch (t) {
                throw $.length = 0, E = 0, t
            }
            for (e = null, $.length = 0, E = 0; h.length;) h.pop()();
            for (let t = 0; t < g.length; t += 1) {
                let e = g[t];
                v.has(e) || (v.add(e), e())
            }
            g.length = 0
        } while ($.length);
        for (; x.length;) x.pop()();
        y = !1, v.clear(), e = t
    }

    let k = new Set;

    class N {
        $destroy() {
            !function(t, e) {
                let n = t.$$;
                null !== n.fragment && (i(n.on_destroy), n.fragment && n.fragment.d(1), n.on_destroy = n.fragment = null, n.ctx = [])
            }(this, 0), this.$destroy = n
        }
        $on(t, e) {
            if (!l(e)) return n;
            let r = this.$$.callbacks[t] || (this.$$.callbacks[t] = []);
            return r.push(e), () => {
                let t = r.indexOf(e);
                -1 !== t && r.splice(t, 1)
            }
        }
        $set(t) {
            this.$$set && 0 !== Object.keys(t).length && (this.$$.skip_bound = !0, this.$$set(t), this.$$.skip_bound = !1)
        }
    }

    function S(e, n, r) {
        let o;
        let i = "",
            { maxLength: l = 1 / 0 } = n;
        o = () => {
            let e = t.event.on("beforeFetch", t => {
                t.data.set("description", i)
            });
            m().$$.on_destroy.push(e)
        },
        m().$$.on_mount.push(o),
        e.$$set = t => {
            "maxLength" in t && r(0, l = t.maxLength)
        };
        return [l, i, function() {
            r(1, i = this.value)
        }]
    }

    class j extends N {
        constructor(t) {
            super();
            function(t, u, c, d, f, s, p, m = [-1]) {
                let h = e;
                e = t;
                let g = t.$$ = {
                    fragment: null,
                    ctx: [],
                    props: s,
                    update: n,
                    not_equal: f,
                    bound: o(),
                    on_mount: [],
                    on_destroy: [],
                    on_disconnect: [],
                    before_update: [],
                    after_update: [],
                    context: new Map(u.context || (h ? h.$$.context : [])),
                    callbacks: o(),
                    dirty: m,
                    skip_bound: !1,
                    root: u.target || h.$$.root
                };
                p && p(g.root);
                let x = !1;
                if (g.ctx = c ? c(t, u.props || {}, (e, n, ...r) => {
                    let o = r.length ? r[0] : n;
                    if (g.ctx && f(g.ctx[e], g.ctx[e] = o) && (!g.skip_bound && g.bound[e] && g.bound[e](o), x)) {
                        var i;
                        -1 === (i = t).$$.dirty[0] && ($.push(i), y || (y = !0, b.then(w)), i.$$.dirty.fill(0)), i.$$.dirty[e / 31 | 0] |= 1 << e % 31
                    }
                    return n
                }) : [], g.update(), x = !0, i(g.before_update), g.fragment = !!d && d(g.ctx), u.target) {
                    if (u.hydrate) {
                        let t = Array.from(u.target.childNodes);
                        g.fragment && g.fragment.l(t), t.forEach(a)
                    } else g.fragment && g.fragment.c();
                    if (u.intro) {
                        var v;
                        (v = t.$$.fragment) && v.i && (k.delete(v), v.i(void 0))
                    }
                    (function(t, e, n, o) {
                        let { fragment: u, after_update: c } = t.$$;
                        u && u.m(e, n), o || _(() => {
                            let e = t.$$.on_mount.map(r).filter(l);
                            t.$$.on_destroy ? t.$$.on_destroy.push(...e) : i(e), t.$$.on_mount = []
                        }), c.forEach(_)
                    })(t, u.target, u.anchor, u.customElement), w()
                }
                e = h
            }
            (this, t, S, A, u, { maxLength: 0 })
        }
    }

    t.event.on("mounted", () => {
        let t = document.querySelector("#description-limit"),
            e = Number.parseInt(t?.value ?? "0") || 1 / 0,
            n = document.querySelector("#file-input .form-group:nth-child(3)");
        if (n) {
            let t = document.createElement("div");
            t.className = "form-group", n.after(t), new j({ target: t, props: { maxLength: e } })
        }
    })
}(blessing);
