(()=>{var e;e=jQuery,"undefined"!=typeof FLBuilder&&FLBuilder.registerModuleHelper("post-carousel",{init:function(){var i=e(".fl-builder-settings").find("select[name=layout]");this._fixForm(),i.on("change",this._fixForm)},_fixForm:function(){var i=e(".fl-builder-settings"),n=i.find("select[name=template]"),t=i.find("div[id=fl-builder-settings-section-info]"),s=i.find("div[id=fl-builder-settings-section-image]");"ask-an-eph"===n.val()?(t.hide(),s.hide()):(t.show(),s.show())}})})();
