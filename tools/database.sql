-- Table: public.miner

-- DROP TABLE public.miner;

CREATE TABLE public.miner
(
    hid integer NOT NULL DEFAULT nextval('miner_hid_seq'::regclass),
    beneficiary text COLLATE pg_catalog."default",
    hash text COLLATE pg_catalog."default",
    miner text COLLATE pg_catalog."default",
    nonce double precision,
    height bigint NOT NULL,
    pow text COLLATE pg_catalog."default",
    prev_hash text COLLATE pg_catalog."default",
    prev_key_hash text COLLATE pg_catalog."default",
    state_hash text COLLATE pg_catalog."default",
    target bigint,
    "time" bigint,
    version bigint,
    orphan boolean,
    remark text COLLATE pg_catalog."default",
    totaltxt text COLLATE pg_catalog."default",
    CONSTRAINT miner_pkey PRIMARY KEY (hid)
)
WITH (
    OIDS = FALSE
)
TABLESPACE pg_default;

ALTER TABLE public.miner
    OWNER to postgres;

-- Index: idx_beneficiary

-- DROP INDEX public.idx_beneficiary;

CREATE INDEX idx_beneficiary
    ON public.miner USING btree
    (beneficiary COLLATE pg_catalog."default")
    TABLESPACE pg_default;

-- Index: idx_height

-- DROP INDEX public.idx_height;

CREATE INDEX idx_height
    ON public.miner USING btree
    (height)
    TABLESPACE pg_default;

-- Index: idx_prevhash

-- DROP INDEX public.idx_prevhash;

CREATE INDEX idx_prevhash
    ON public.miner USING btree
    (prev_hash COLLATE pg_catalog."default")
    TABLESPACE pg_default;
    
    
    
    
    
    
    
  -- Table: public.microblock

-- DROP TABLE public.microblock;

CREATE TABLE public.microblock
(
    hid bigint NOT NULL DEFAULT nextval('microblock_hid_seq'::regclass),
    hash text COLLATE pg_catalog."default",
    height bigint,
    pof_hash text COLLATE pg_catalog."default",
    prev_hash text COLLATE pg_catalog."default",
    prev_key_hash text COLLATE pg_catalog."default",
    signature text COLLATE pg_catalog."default",
    state_hash text COLLATE pg_catalog."default",
    "time" bigint,
    txs_hash text COLLATE pg_catalog."default",
    version bigint,
    remark text COLLATE pg_catalog."default",
    CONSTRAINT microblock_pkey PRIMARY KEY (hid)
)
WITH (
    OIDS = FALSE
)
TABLESPACE pg_default;

ALTER TABLE public.microblock
    OWNER to postgres;

-- Index: idx_microblockhash

-- DROP INDEX public.idx_microblockhash;

CREATE INDEX idx_microblockhash
    ON public.microblock USING btree
    (hash COLLATE pg_catalog."default")
    TABLESPACE pg_default;
    
    
    
-- Table: public.transactions

-- DROP TABLE public.transactions;

CREATE TABLE public.transactions
(
    tid bigint NOT NULL DEFAULT nextval('transactions_tid_seq'::regclass),
    block_hash text COLLATE pg_catalog."default",
    block_height bigint,
    hash text COLLATE pg_catalog."default",
    signatures text COLLATE pg_catalog."default",
    amount numeric,
    fee numeric,
    nonce numeric,
    payload text COLLATE pg_catalog."default",
    recipient_id text COLLATE pg_catalog."default",
    sender_id text COLLATE pg_catalog."default",
    ttl bigint,
    type text COLLATE pg_catalog."default",
    version bigint,
    remark text COLLATE pg_catalog."default",
    remark2 text COLLATE pg_catalog."default",
    CONSTRAINT transactions_pkey PRIMARY KEY (tid)
)
WITH (
    OIDS = FALSE
)
TABLESPACE pg_default;

ALTER TABLE public.transactions
    OWNER to postgres;

-- Index: idx_recipient_id

-- DROP INDEX public.idx_recipient_id;

CREATE INDEX idx_recipient_id
    ON public.transactions USING btree
    (recipient_id COLLATE pg_catalog."default")
    TABLESPACE pg_default;

-- Index: idx_sender_id

-- DROP INDEX public.idx_sender_id;

CREATE INDEX idx_sender_id
    ON public.transactions USING btree
    (sender_id COLLATE pg_catalog."default")
    TABLESPACE pg_default;

-- Index: idx_transactionhash

-- DROP INDEX public.idx_transactionhash;

CREATE INDEX idx_transactionhash
    ON public.transactions USING btree
    (hash COLLATE pg_catalog."default")
    TABLESPACE pg_default;
    
    
        
    
    
-- Table: public.aeinflation

-- DROP TABLE public.aeinflation;

CREATE TABLE public.aeinflation
(
    iid bigint NOT NULL DEFAULT nextval('aeinflation_iid_seq'::regclass),
    blockid bigint,
    reward bigint,
    totalamount double precision,
    inflation double precision,
    CONSTRAINT aeinflation_pkey PRIMARY KEY (iid)
)
WITH (
    OIDS = FALSE
)
TABLESPACE pg_default;

ALTER TABLE public.aeinflation
    OWNER to postgres;

-- Index: idx_blockid

-- DROP INDEX public.idx_blockid;

CREATE INDEX idx_blockid
    ON public.aeinflation USING btree
    (blockid)
    TABLESPACE pg_default;
    
    
    
    
    
     
    
-- Table: public.aenetwork

-- DROP TABLE public.aenetwork;

CREATE TABLE public.aenetwork
(
    rid bigint NOT NULL DEFAULT nextval('aenetwork_rid_seq'::regclass),
    difficulty bigint,
    peercount bigint,
    minercount bigint,
    remark text COLLATE pg_catalog."default",
    recordtime bigint,
    CONSTRAINT aenetwork_pkey PRIMARY KEY (rid)
)
WITH (
    OIDS = FALSE
)
TABLESPACE pg_default;

ALTER TABLE public.aenetwork
    OWNER to postgres;
    
    
  
    
    
    
    
    
    
    
    
    
    
    
    
