'use strict';
(() => {
  // Bearer token kept only in closure memory; never localStorage or URL.
  let token = '';
  const $ = id => document.getElementById(id);
  const navItems = [ ['overview','Overview'], ['courses','Courses'], ['media','Media'], ['events','Events'], ['credentials','Credential Queue'], ['audit','Audit Logs'], ['releases','Release Drafts'], ['incidents','Incident Drafts'] ];
  async function api(path,method='GET',data=null) {
    const response=await fetch('/api'+path,{method,cache:'no-store',credentials:'omit',headers:{'Accept':'application/json',...(token?{'Authorization':'Bearer '+token}:{}),...(data?{'Content-Type':'application/json'}:{})},body:data?JSON.stringify(data):undefined});
    const value=await response.json().catch(()=>({error:'Invalid server response'}));
    if(!response.ok)throw new Error(value.error||('HTTP '+response.status));
    return value;
  }
  function elem(tag,text='',cls='') {const e=document.createElement(tag);e.textContent=String(text);if(cls)e.className=cls;return e;}
  function renderRows(parent,records,fields) {parent.replaceChildren();if(records.length===0){parent.append(elem('p','No records available'));return;}
    for(const rec of records){const box=elem('div','','item');for(const key of fields){box.append(elem('div',`${key.replaceAll('_',' ')}: ${rec[key]??'—'}`));}parent.append(box);}}
  function panel(title,description=''){const c=$('content');c.replaceChildren(elem('h2',title),elem('p',description));return c;}
  function formField(form,name,type='text',placeholder='') {const label=elem('label',name.replaceAll('_',' '));const input=document.createElement(type==='textarea'?'textarea':'input');if(type!=='textarea')input.type=type;input.name=name;input.required=true;input.placeholder=placeholder;label.append(input);form.append(label);return input;}
  async function choose(id){const c=panel(navItems.find(x=>x[0]===id)?.[1]||'Admin');const block=elem('div','Loading...');c.append(block);
    try{
      if(id==='overview'){const r=await api('/v17/admin/overview');block.replaceChildren();$('stats').replaceChildren();for(const [name,count] of Object.entries({...r.stats,unverified_doctors:r.unverified_doctors,release_drafts:r.release_drafts})){const card=elem('div','','stat');card.append(elem('strong',count),elem('span',name.replaceAll('_',' ')));$('stats').append(card);}block.append(elem('p','Live database counts. Not a production security or clinical safety approval.'));return;}
      $('stats').replaceChildren();
      if(['courses','media','events'].includes(id)){
        const r=await api('/v17/admin/'+id);block.replaceChildren();const form=elem('form');const title=formField(form,'title','text','Draft title');
        let secondary,choice;
        if(id==='courses'){secondary=formField(form,'summary','textarea','Educational summary');}
        if(id==='media'||id==='events'){choice=elem('select');const options=id==='media'?['series_tv','audio_ebook','ebook']:['qa','workshop','meditation'];for(const v of options){const opt=elem('option',v);opt.value=v;choice.append(opt);}const label=elem('label',id==='media'?'Media type':'Event type');label.append(choice);form.append(label);}
        if(id==='events')secondary=formField(form,'starts_at','text','2026-12-04 10:00:00 (UTC)');
        const submit=elem('button','Create unapproved DRAFT');submit.type='submit';form.append(submit);
        form.addEventListener('submit',async e=>{e.preventDefault();const data={title:title.value};if(id==='courses')data.summary=secondary.value;if(id==='media')data.kind=choice.value;if(id==='events'){data.event_type=choice.value;data.starts_at=secondary.value;}try{await api('/v17/admin/'+id,'POST',data);choose(id);}catch(err){alert(err.message)}});
        block.append(elem('p','Drafts only. Publishing requires additional editorial, rights, clinical review and authorized workflow.'));block.append(form);
        const list=elem('div');block.append(list);renderRows(list,r.data,id==='courses'?['id','title','state','clinical_review_status']:id==='media'?['id','title','kind','rights_status','state']:['id','title','event_type','starts_at','state']);return;
      }
      if(id==='credentials'){const r=await api('/v17/admin/credential-queue');block.replaceChildren(elem('p','PENDING only. Verify license with external licensing authority, record evidence & independent approval before enabling bookings.'));renderRows(block,r.data,['id','display_name','specialty','license_issuer','license_number']);return;}
      if(id==='audit'){const r=await api('/v17/admin/audit');renderRows(block,r.data,['id','actor_id','event','created_at']);return;}
      if(id==='releases'){const r=await api('/v17/admin/releases');block.replaceChildren();const form=elem('form');const tag=formField(form,'version_tag','text','1.7.1');const notes=formField(form,'notes','textarea','Internal release notes, at least 8 characters');const submit=elem('button','Create DRAFT (not deploy)');submit.type='submit';form.append(submit);form.addEventListener('submit',async e=>{e.preventDefault();try{await api('/v17/admin/releases','POST',{version_tag:tag.value,notes:notes.value});choose('releases');}catch(err){alert(err.message)}});block.append(form);const list=elem('div');block.append(list);renderRows(list,r.data,['version_tag','state','created_at']);return;}
      if(id==='incidents'){const r=await api('/v17/admin/incidents');block.replaceChildren();const form=elem('form');const category=formField(form,'category','text','Service access');const summary=formField(form,'summary','textarea','No clinical patient identifiers.');const select=elem('select');for(const v of ['low','medium','high']){const opt=elem('option',v);opt.value=v;select.append(opt);}const label=elem('label','severity');label.append(select);form.append(label);const button=elem('button','Create incident DRAFT');button.type='submit';form.append(button);form.addEventListener('submit',async e=>{e.preventDefault();try{await api('/v17/admin/incidents','POST',{category:category.value,summary:summary.value,severity:select.value});choose('incidents');}catch(err){alert(err.message)}});block.append(form);const list=elem('div');block.append(list);renderRows(list,r.data,['id','category','severity','state','created_at']);}
    }catch(err){block.textContent='Request failed: '+err.message;}
  }
  $('login-form').addEventListener('submit',async e=>{e.preventDefault();const pass=$('password').value;try{const r=await api('/auth/login','POST',{email:$('email').value,password:pass});if(r.user?.role!=='admin')throw new Error('Admin role required');token=r.token;$('password').value='';$('login-panel').hidden=true;$('dashboard').hidden=false;$('logout').hidden=false;const nav=$('nav');nav.replaceChildren();for(const [id,name] of navItems){const b=elem('button',name);b.type='button';b.addEventListener('click',()=>choose(id));nav.append(b);}choose('overview');}catch(err){token='';$('password').value='';$('login-status').textContent=err.message;}});
  $('logout').addEventListener('click',async()=>{try{if(token)await api('/auth/logout','POST');}catch(_){}token='';$('dashboard').hidden=true;$('login-panel').hidden=false;$('logout').hidden=true;$('stats').replaceChildren();});
})();
